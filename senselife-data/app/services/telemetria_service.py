from datetime import datetime, timezone

from app.db.mongo import get_database
from app.models.schemas import TelemetriaIngest, TelemetriaLectura
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
