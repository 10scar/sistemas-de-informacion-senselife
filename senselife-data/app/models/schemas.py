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
    estado: str = "pendiente"
    tipo: Literal["critico", "alerta"]
