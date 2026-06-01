from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")

    mongodb_uri: str = "mongodb://localhost:27017"
    mongodb_database: str = "senselife_data"
    internal_token: str = "tu_token_secreto_aqui"
    senselife_system_url: str = "http://localhost"
    alert_dedup_seconds: int = 300

    fc_critico_alto: float = 180.0
    fc_alerta_alto: float = 160.0
    fc_alerta_bajo: float = 100.0
    fc_critico_bajo: float = 80.0
    fr_critico_alto: float = 70.0
    fr_alerta_alto: float = 60.0
    fr_alerta_bajo: float = 25.0
    fr_critico_bajo: float = 20.0

    simulador_intervalo_seg: float = 2.0

    dispositivos_file: str = "data/dispositivos.json"
    simulador_estado_file: str = "data/simulador_estado.json"


@lru_cache
def get_settings() -> Settings:
    return Settings()
