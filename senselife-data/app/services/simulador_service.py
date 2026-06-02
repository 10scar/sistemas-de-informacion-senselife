import asyncio
import random
from datetime import datetime, timezone

from app.core.config import get_settings
from app.models.schemas import DispositivoSim, TelemetriaIngest
from app.services import simulador_storage
from app.services.telemetria_service import ingestar_lectura

_simulador_tasks: dict[int, asyncio.Task] = {}

# Rangos neonatales para el simulador (PALS/RCH; ver README «Simulador y rangos vitales»).
RANGOS_SIMULADOR: dict[str, dict[str, tuple[float, float]]] = {
    "normal": {"fc": (120.0, 160.0), "fr": (35.0, 55.0)},
    "bajo": {"fc": (70.0, 85.0), "fr": (18.0, 24.0)},
    "alto": {"fc": (175.0, 195.0), "fr": (62.0, 72.0)},
}


def _valores_por_modo(modo: str) -> tuple[float, float]:
    rangos = RANGOS_SIMULADOR.get(modo, RANGOS_SIMULADOR["normal"])
    fc_min, fc_max = rangos["fc"]
    fr_min, fr_max = rangos["fr"]
    return random.uniform(fc_min, fc_max), random.uniform(fr_min, fr_max)


async def listar_dispositivos_sim() -> list[DispositivoSim]:
    return simulador_storage.listar_dispositivos_merged()


def catalog_metadata() -> dict:
    return {
        "exported_at": simulador_storage.catalog_exported_at(),
        "catalog_path": str(simulador_storage.dispositivos_catalog_path()),
        "estado_path": str(simulador_storage.simulador_estado_path()),
    }


async def _loop_simulacion(id_dispositivo: int) -> None:
    settings = get_settings()

    while True:
        st = simulador_storage.obtener_estado_simulacion(id_dispositivo)
        if st is None or not st.get("simulacion_activa"):
            break

        modo = st.get("modo_simulacion", "normal")
        fc, fr = _valores_por_modo(modo)
        lectura = await ingestar_lectura(
            TelemetriaIngest(
                id_dispositivo=id_dispositivo,
                frecuencia_cardiaca=fc,
                frecuencia_respiratoria=fr,
                tiempo=datetime.now(timezone.utc),
            ),
        )

        simulador_storage.actualizar_estado(
            id_dispositivo,
            ultima_fc=lectura.frecuencia_cardiaca,
            ultima_fr=lectura.frecuencia_respiratoria,
            lecturas_generadas=int(st.get("lecturas_generadas", 0)) + 1,
        )

        await asyncio.sleep(settings.simulador_intervalo_seg)


def _iniciar_task(id_dispositivo: int) -> None:
    if id_dispositivo in _simulador_tasks and not _simulador_tasks[id_dispositivo].done():
        return
    _simulador_tasks[id_dispositivo] = asyncio.create_task(_loop_simulacion(id_dispositivo))


def _detener_task(id_dispositivo: int) -> None:
    task = _simulador_tasks.pop(id_dispositivo, None)
    if task is not None and not task.done():
        task.cancel()


async def toggle_simulacion(id_dispositivo: int) -> DispositivoSim | None:
    st = simulador_storage.obtener_estado_simulacion(id_dispositivo)
    if st is None:
        return None

    nuevo_estado = not bool(st.get("simulacion_activa"))
    simulador_storage.actualizar_estado(id_dispositivo, simulacion_activa=nuevo_estado)

    if nuevo_estado:
        _iniciar_task(id_dispositivo)
    else:
        _detener_task(id_dispositivo)

    dispositivos = await listar_dispositivos_sim()
    return next((d for d in dispositivos if d.id_dispositivo == id_dispositivo), None)


async def pulso_simulacion(id_dispositivo: int, modo: str) -> DispositivoSim | None:
    if simulador_storage.obtener_estado_simulacion(id_dispositivo) is None:
        return None

    if modo not in ("normal", "bajo", "alto"):
        modo = "normal"

    fc, fr = _valores_por_modo(modo)
    lectura = await ingestar_lectura(
        TelemetriaIngest(
            id_dispositivo=id_dispositivo,
            frecuencia_cardiaca=fc,
            frecuencia_respiratoria=fr,
            tiempo=datetime.now(timezone.utc),
        ),
    )

    st = simulador_storage.obtener_estado_simulacion(id_dispositivo) or {}
    simulador_storage.actualizar_estado(
        id_dispositivo,
        modo_simulacion=modo,
        ultima_fc=lectura.frecuencia_cardiaca,
        ultima_fr=lectura.frecuencia_respiratoria,
        lecturas_generadas=int(st.get("lecturas_generadas", 0)) + 1,
    )

    dispositivos = await listar_dispositivos_sim()
    return next((d for d in dispositivos if d.id_dispositivo == id_dispositivo), None)


async def reanudar_simulaciones_activas() -> None:
    dispositivos = await listar_dispositivos_sim()
    for dispositivo in dispositivos:
        if dispositivo.simulacion_activa:
            _iniciar_task(dispositivo.id_dispositivo)
