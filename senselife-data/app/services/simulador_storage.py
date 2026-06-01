import json
from pathlib import Path

from app.core.config import get_settings
from app.models.schemas import DispositivoSim

def _resolve(path_setting: str) -> Path:
    path = Path(path_setting)
    if not path.is_absolute():
        path = Path(__file__).resolve().parents[2] / path
    return path


def dispositivos_catalog_path() -> Path:
    return _resolve(get_settings().dispositivos_file)


def simulador_estado_path() -> Path:
    return _resolve(get_settings().simulador_estado_file)


def _default_catalog() -> dict:
    return {"exported_at": None, "dispositivos": []}


def load_catalog() -> dict:
    path = dispositivos_catalog_path()
    if not path.exists():
        return _default_catalog()
    with path.open(encoding="utf-8") as handle:
        data = json.load(handle)
    if isinstance(data, list):
        return {"exported_at": None, "dispositivos": data}
    return data


def load_estado() -> dict[str, dict]:
    path = simulador_estado_path()
    if not path.exists():
        return {}
    with path.open(encoding="utf-8") as handle:
        raw = json.load(handle)
    return {str(key): value for key, value in raw.items()}


def save_estado(estado: dict[str, dict]) -> None:
    path = simulador_estado_path()
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as handle:
        json.dump(estado, handle, indent=2, ensure_ascii=False)


def _estado_para(id_dispositivo: int, estado: dict[str, dict]) -> dict:
    key = str(id_dispositivo)
    if key not in estado:
        estado[key] = {
            "simulacion_activa": False,
            "modo_simulacion": "normal",
            "ultima_fc": None,
            "ultima_fr": None,
            "lecturas_generadas": 0,
        }
    return estado[key]


def listar_dispositivos_merged() -> list[DispositivoSim]:
    catalog = load_catalog()
    estado = load_estado()
    items: list[DispositivoSim] = []

    for row in catalog.get("dispositivos", []):
        device_id = int(row["id"])
        st = _estado_para(device_id, estado)
        items.append(
            DispositivoSim(
                id_dispositivo=device_id,
                numero_serie=str(row.get("numero_serie") or f"SL-{device_id:03d}"),
                ubicacion=row.get("ubicacion"),
                centro_medico_id=row.get("centro_medico_id"),
                estado_dispositivo=row.get("estado"),
                simulacion_activa=bool(st.get("simulacion_activa", False)),
                modo_simulacion=st.get("modo_simulacion", "normal"),
                ultima_fc=st.get("ultima_fc"),
                ultima_fr=st.get("ultima_fr"),
                lecturas_generadas=int(st.get("lecturas_generadas", 0)),
            ),
        )

    save_estado(estado)
    return sorted(items, key=lambda d: d.id_dispositivo)


def catalog_exported_at() -> str | None:
    return load_catalog().get("exported_at")


def dispositivo_existe(id_dispositivo: int) -> bool:
    catalog = load_catalog()
    ids = {int(d["id"]) for d in catalog.get("dispositivos", [])}
    return id_dispositivo in ids


def actualizar_estado(id_dispositivo: int, **campos) -> dict | None:
    if not dispositivo_existe(id_dispositivo):
        return None
    estado = load_estado()
    st = _estado_para(id_dispositivo, estado)
    st.update(campos)
    save_estado(estado)
    return st


def obtener_estado_simulacion(id_dispositivo: int) -> dict | None:
    if not dispositivo_existe(id_dispositivo):
        return None
    estado = load_estado()
    return _estado_para(id_dispositivo, estado)
