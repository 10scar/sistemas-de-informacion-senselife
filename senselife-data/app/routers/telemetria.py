from datetime import datetime

from fastapi import APIRouter, Depends, HTTPException, Query, status

from app.core.security import verify_internal_token
from app.models.schemas import TelemetriaIngest, TelemetriaLectura
from app.services.telemetria_service import (
    ingestar_lectura,
    obtener_historial,
    obtener_ultima_lectura,
)

router = APIRouter(
    prefix="/api/v1/telemetria",
    tags=["telemetria"],
    dependencies=[Depends(verify_internal_token)],
)


@router.post("/ingest", status_code=status.HTTP_201_CREATED, response_model=TelemetriaLectura)
async def ingest(payload: TelemetriaIngest) -> TelemetriaLectura:
    return await ingestar_lectura(payload)


@router.get("/{id_dispositivo:int}/actual", response_model=TelemetriaLectura)
async def actual(id_dispositivo: int) -> TelemetriaLectura:
    lectura = await obtener_ultima_lectura(id_dispositivo)
    if lectura is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Sin lecturas para el dispositivo.")
    return lectura


@router.get("/{id_dispositivo:int}", response_model=list[TelemetriaLectura])
async def historial(
    id_dispositivo: int,
    fecha_inicio: datetime = Query(...),
    fecha_fin: datetime = Query(...),
) -> list[TelemetriaLectura]:
    if fecha_fin < fecha_inicio:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="fecha_fin debe ser posterior a fecha_inicio.",
        )
    return await obtener_historial(id_dispositivo, fecha_inicio, fecha_fin)
