from datetime import datetime, timezone

import httpx

from app.core.config import Settings, get_settings
from app.models.schemas import AlertaCreate, TelemetriaLectura


def evaluar_tipo_alerta(fc: float, fr: float, settings: Settings) -> str | None:
    if fc >= settings.fc_critico_alto or fr >= settings.fr_critico_alto:
        return "critico"
    if fc <= settings.fc_critico_bajo or fr <= settings.fr_critico_bajo:
        return "critico"
    if fc >= settings.fc_alerta_alto or fr >= settings.fr_alerta_alto:
        return "alerta"
    if fc <= settings.fc_alerta_bajo or fr <= settings.fr_alerta_bajo:
        return "alerta"
    return None


async def obtener_contexto_dispositivo(id_dispositivo: int) -> str | None:
    settings = get_settings()
    url = f"{settings.senselife_system_url.rstrip('/')}/api/v1/dispositivos/{id_dispositivo}/contexto"
    headers = {"x-internal-token": settings.internal_token, "Accept": "application/json"}

    async with httpx.AsyncClient(timeout=10.0) as client:
        try:
            response = await client.get(url, headers=headers)
            if response.status_code == 404:
                return None
            response.raise_for_status()
            data = response.json()
            return data.get("id_paciente")
        except httpx.HTTPError:
            return None


async def registrar_alerta_en_monolito(
    id_paciente: str,
    lectura: TelemetriaLectura,
    tipo: str,
) -> bool:
    settings = get_settings()
    url = f"{settings.senselife_system_url.rstrip('/')}/api/v1/alertas"
    headers = {
        "x-internal-token": settings.internal_token,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    payload = AlertaCreate(
        id_paciente=id_paciente,
        id_telemetria=lectura.id,
        frecuencia_cardiaca=lectura.frecuencia_cardiaca,
        frecuencia_respiratoria=lectura.frecuencia_respiratoria,
        estado="pendiente",
        tipo=tipo,  # type: ignore[arg-type]
    )

    async with httpx.AsyncClient(timeout=10.0) as client:
        try:
            response = await client.post(url, headers=headers, json=payload.model_dump())
            return response.status_code in (200, 201)
        except httpx.HTTPError:
            return False


async def procesar_alertas_tras_ingesta(lectura: TelemetriaLectura) -> None:
    settings = get_settings()
    tipo = evaluar_tipo_alerta(
        lectura.frecuencia_cardiaca,
        lectura.frecuencia_respiratoria,
        settings,
    )
    if tipo is None:
        return

    id_paciente = await obtener_contexto_dispositivo(lectura.id_dispositivo)
    if id_paciente is None:
        return

    await registrar_alerta_en_monolito(id_paciente, lectura, tipo)
