from datetime import datetime, timezone

from app.db.mongo import get_database
from app.models.schemas import (
    TelemetriaIngest,
    TelemetriaLectura,
    TelemetriaResumenRequest,
    TelemetriaResumenResponse,
    TelemetriaResumenStats,
    TelemetriaSeriePunto,
    TelemetriaVentana,
)
from app.services.alerta_service import procesar_alertas_tras_ingesta
from app.services.counter_service import next_telemetria_id


def _doc_to_lectura(doc: dict) -> TelemetriaLectura:
    tiempo = doc["tiempo"]
    if isinstance(tiempo, str):
        tiempo = datetime.fromisoformat(tiempo.replace("Z", "+00:00"))
    return TelemetriaLectura(
        id=int(doc["id"]),
        id_dispositivo=int(doc["id_dispositivo"]),
        frecuencia_cardiaca=float(doc["frecuencia_cardiaca"]),
        frecuencia_respiratoria=float(doc["frecuencia_respiratoria"]),
        tiempo=tiempo,
    )


async def ingestar_lectura(payload: TelemetriaIngest, *, evaluar_alertas: bool = True) -> TelemetriaLectura:
    db = get_database()
    lectura_id = await next_telemetria_id()
    tiempo = payload.tiempo or datetime.now(timezone.utc)

    doc = {
        "id": lectura_id,
        "id_dispositivo": payload.id_dispositivo,
        "frecuencia_cardiaca": payload.frecuencia_cardiaca,
        "frecuencia_respiratoria": payload.frecuencia_respiratoria,
        "tiempo": tiempo,
    }
    await db.telemetria_frecuencia.insert_one(doc)
    lectura = _doc_to_lectura(doc)

    if evaluar_alertas:
        await procesar_alertas_tras_ingesta(lectura)

    return lectura


async def obtener_ultima_lectura(id_dispositivo: int) -> TelemetriaLectura | None:
    db = get_database()
    doc = await db.telemetria_frecuencia.find_one(
        {"id_dispositivo": id_dispositivo},
        sort=[("tiempo", -1)],
    )
    if doc is None:
        return None
    return _doc_to_lectura(doc)


async def obtener_historial(
    id_dispositivo: int,
    fecha_inicio: datetime,
    fecha_fin: datetime,
) -> list[TelemetriaLectura]:
    db = get_database()
    cursor = db.telemetria_frecuencia.find(
        {
            "id_dispositivo": id_dispositivo,
            "tiempo": {"$gte": fecha_inicio, "$lte": fecha_fin},
        },
        sort=[("tiempo", 1)],
    )
    return [_doc_to_lectura(doc) async for doc in cursor]


def _normalizar_tiempo(tiempo: datetime) -> datetime:
    if tiempo.tzinfo is None:
        return tiempo.replace(tzinfo=timezone.utc)
    return tiempo.astimezone(timezone.utc)


def _calcular_tendencia(valores: list[float]) -> int | None:
    if len(valores) < 4:
        return None

    mid = len(valores) // 2
    primera = valores[:mid]
    segunda = valores[mid:]
    media_anterior = sum(primera) / len(primera)
    media_actual = sum(segunda) / len(segunda)

    if media_anterior == 0.0:
        return None

    return int(round(((media_actual - media_anterior) / media_anterior) * 100))


def _muestra_sparkline(valores: list[float], max_puntos: int = 24) -> list[float]:
    n = len(valores)
    if n <= max_puntos:
        return valores

    paso = (n + max_puntos - 1) // max_puntos
    muestra = [valores[i] for i in range(0, n, paso)]
    if muestra[-1] != valores[-1]:
        muestra.append(valores[-1])

    return muestra


def _downsample_serie(serie: list[TelemetriaSeriePunto], max_puntos: int) -> list[TelemetriaSeriePunto]:
    n = len(serie)
    if n <= max_puntos:
        return serie

    paso = (n + max_puntos - 1) // max_puntos
    muestra = [serie[i] for i in range(0, n, paso)]
    if muestra[-1].tiempo != serie[-1].tiempo:
        muestra.append(serie[-1])

    return muestra


def _tiempo_a_ms(tiempo: datetime) -> int:
    return int(_normalizar_tiempo(tiempo).timestamp() * 1000)


async def _serie_buckets_mongo(db, match: dict, bucket_ms: int) -> list[dict]:
    return await db.telemetria_frecuencia.aggregate(
        [
            {"$match": match},
            {
                "$group": {
                    "_id": {
                        "$subtract": [
                            {"$toLong": "$tiempo"},
                            {"$mod": [{"$toLong": "$tiempo"}, bucket_ms]},
                        ],
                    },
                    "frecuencia_cardiaca": {"$avg": "$frecuencia_cardiaca"},
                    "frecuencia_respiratoria": {"$avg": "$frecuencia_respiratoria"},
                },
            },
            {"$sort": {"_id": 1}},
        ],
    ).to_list(length=None)


async def _serie_buckets_python(db, match: dict, bucket_ms: int) -> list[dict]:
    buckets: dict[int, dict[str, list[float]]] = {}
    cursor = db.telemetria_frecuencia.find(
        match,
        {"tiempo": 1, "frecuencia_cardiaca": 1, "frecuencia_respiratoria": 1},
    ).sort("tiempo", 1)

    async for doc in cursor:
        ts_ms = _tiempo_a_ms(doc["tiempo"])
        bucket_id = ts_ms - (ts_ms % bucket_ms)
        if bucket_id not in buckets:
            buckets[bucket_id] = {"fc": [], "fr": []}
        buckets[bucket_id]["fc"].append(float(doc["frecuencia_cardiaca"]))
        buckets[bucket_id]["fr"].append(float(doc["frecuencia_respiratoria"]))

    return [
        {
            "_id": bucket_id,
            "frecuencia_cardiaca": sum(datos["fc"]) / len(datos["fc"]),
            "frecuencia_respiratoria": sum(datos["fr"]) / len(datos["fr"]),
        }
        for bucket_id, datos in sorted(buckets.items())
    ]


async def _obtener_serie_buckets(db, match: dict, bucket_ms: int) -> list[dict]:
    try:
        return await _serie_buckets_mongo(db, match, bucket_ms)
    except (TypeError, NotImplementedError):
        return await _serie_buckets_python(db, match, bucket_ms)


async def _resumen_ventana(
    ventana: TelemetriaVentana,
    bucket_segundos: int,
) -> tuple[TelemetriaResumenStats, list[TelemetriaSeriePunto], list[float]]:
    db = get_database()
    fecha_inicio = _normalizar_tiempo(ventana.fecha_inicio)
    fecha_fin = _normalizar_tiempo(ventana.fecha_fin)
    bucket_ms = bucket_segundos * 1000

    match = {
        "id_dispositivo": ventana.id_dispositivo,
        "tiempo": {"$gte": fecha_inicio, "$lte": fecha_fin},
    }

    stats_docs = await db.telemetria_frecuencia.aggregate(
        [
            {"$match": match},
            {
                "$group": {
                    "_id": None,
                    "promedio_fc": {"$avg": "$frecuencia_cardiaca"},
                    "min_fc": {"$min": "$frecuencia_cardiaca"},
                    "max_fc": {"$max": "$frecuencia_cardiaca"},
                    "conteo": {"$sum": 1},
                },
            },
        ],
    ).to_list(length=1)

    if not stats_docs:
        return TelemetriaResumenStats(), [], []

    raw_stats = stats_docs[0]
    stats = TelemetriaResumenStats(
        promedio_fc=round(float(raw_stats["promedio_fc"]), 1) if raw_stats.get("promedio_fc") is not None else None,
        min_fc=float(raw_stats["min_fc"]) if raw_stats.get("min_fc") is not None else None,
        max_fc=float(raw_stats["max_fc"]) if raw_stats.get("max_fc") is not None else None,
        conteo=int(raw_stats.get("conteo", 0)),
    )

    bucket_docs = await _obtener_serie_buckets(db, match, bucket_ms)

    serie: list[TelemetriaSeriePunto] = []
    fc_crudos: list[float] = []

    for doc in bucket_docs:
        tiempo = datetime.fromtimestamp(doc["_id"] / 1000, tz=timezone.utc)
        fc = round(float(doc["frecuencia_cardiaca"]), 1)
        fr = round(float(doc["frecuencia_respiratoria"]), 1)
        serie.append(
            TelemetriaSeriePunto(
                tiempo=tiempo,
                frecuencia_cardiaca=fc,
                frecuencia_respiratoria=fr,
            ),
        )
        fc_crudos.append(fc)

    stats.tendencia_pct = _calcular_tendencia(fc_crudos)

    return stats, serie, fc_crudos


def _fusionar_stats(stats_list: list[TelemetriaResumenStats]) -> TelemetriaResumenStats:
    stats_validos = [s for s in stats_list if s.conteo > 0]
    if not stats_validos:
        return TelemetriaResumenStats()

    conteo_total = sum(s.conteo for s in stats_validos)
    promedio_ponderado = sum((s.promedio_fc or 0.0) * s.conteo for s in stats_validos) / conteo_total
    min_fc = min(s.min_fc for s in stats_validos if s.min_fc is not None)
    max_fc = max(s.max_fc for s in stats_validos if s.max_fc is not None)

    fc_todos: list[float] = []
    for stats in stats_validos:
        if stats.promedio_fc is not None and stats.conteo > 0:
            fc_todos.extend([stats.promedio_fc] * stats.conteo)

    return TelemetriaResumenStats(
        promedio_fc=round(promedio_ponderado, 1),
        min_fc=min_fc,
        max_fc=max_fc,
        conteo=conteo_total,
        tendencia_pct=_calcular_tendencia(fc_todos) if len(fc_todos) >= 4 else None,
    )


async def obtener_resumen(payload: TelemetriaResumenRequest) -> TelemetriaResumenResponse:
    todas_series: list[TelemetriaSeriePunto] = []
    stats_por_ventana: list[TelemetriaResumenStats] = []
    fc_spark: list[float] = []

    for ventana in payload.ventanas:
        if ventana.fecha_fin < ventana.fecha_inicio:
            continue

        stats, serie, fc_crudos = await _resumen_ventana(ventana, payload.bucket_segundos)
        stats_por_ventana.append(stats)
        todas_series.extend(serie)
        fc_spark.extend(fc_crudos)

    todas_series.sort(key=lambda p: p.tiempo)
    serie = _downsample_serie(todas_series, payload.max_puntos)

    stats_fusionados = _fusionar_stats(stats_por_ventana)
    if stats_fusionados.conteo > 0 and serie:
        fc_serie = [p.frecuencia_cardiaca for p in serie]
        stats_fusionados.tendencia_pct = _calcular_tendencia(fc_serie)

    sparkline = _muestra_sparkline(fc_spark if fc_spark else [p.frecuencia_cardiaca for p in serie])

    return TelemetriaResumenResponse(
        stats=stats_fusionados,
        sparkline_fc=sparkline,
        serie=serie,
    )
