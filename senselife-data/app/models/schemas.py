from datetime import datetime
from typing import Literal

from pydantic import BaseModel, Field


class TelemetriaIngest(BaseModel):
    id_dispositivo: int
    frecuencia_cardiaca: float = Field(..., gt=0)
    frecuencia_respiratoria: float = Field(..., gt=0)
    tiempo: datetime | None = None


class TelemetriaLectura(BaseModel):
    id: int
    id_dispositivo: int
    frecuencia_cardiaca: float
    frecuencia_respiratoria: float
    tiempo: datetime


class TelemetriaVentana(BaseModel):
    id_dispositivo: int
    fecha_inicio: datetime
    fecha_fin: datetime


class TelemetriaResumenRequest(BaseModel):
    ventanas: list[TelemetriaVentana] = Field(..., min_length=1)
    bucket_segundos: int = Field(..., gt=0)
    max_puntos: int = Field(default=120, ge=10, le=500)


class TelemetriaResumenStats(BaseModel):
    promedio_fc: float | None = None
    min_fc: float | None = None
    max_fc: float | None = None
    conteo: int = 0
    tendencia_pct: int | None = None


class TelemetriaSeriePunto(BaseModel):
    tiempo: datetime
    frecuencia_cardiaca: float
    frecuencia_respiratoria: float


class TelemetriaResumenResponse(BaseModel):
    stats: TelemetriaResumenStats
    sparkline_fc: list[float]
    serie: list[TelemetriaSeriePunto]


class DispositivoSim(BaseModel):
    id_dispositivo: int
    numero_serie: str
    ubicacion: str | None = None
    centro_medico_id: int | None = None
    estado_dispositivo: str | None = None
    simulacion_activa: bool = False
    modo_simulacion: Literal["normal", "bajo", "alto"] = "normal"
    ultima_fc: float | None = None
    ultima_fr: float | None = None
    lecturas_generadas: int = 0


class DispositivoContexto(BaseModel):
    id_dispositivo: int
    id_paciente: str | None = None


class AlertaCreate(BaseModel):
    id_paciente: str
    id_telemetria: int
    frecuencia_cardiaca: float | None = None
    frecuencia_respiratoria: float | None = None
    estado: str = "pendiente"
    tipo: Literal["critico", "alerta"]
