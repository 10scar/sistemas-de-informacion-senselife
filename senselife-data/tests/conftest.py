import json
import os
from pathlib import Path

import pytest
import pytest_asyncio
from httpx import ASGITransport, AsyncClient
from mongomock_motor import AsyncMongoMockClient

os.environ["INTERNAL_TOKEN"] = "test-token"
os.environ["MONGODB_URI"] = "mongodb://localhost:27017"
os.environ["MONGODB_DATABASE"] = "senselife_data_test"
os.environ["SENSELIFE_SYSTEM_URL"] = "http://laravel.test"


@pytest.fixture(autouse=True)
def _reset_settings_cache():
    from app.core.config import get_settings

    get_settings.cache_clear()
    yield
    get_settings.cache_clear()


@pytest.fixture
def anyio_backend():
    return "asyncio"


@pytest.fixture
def simulador_files(tmp_path, monkeypatch):
    catalog = tmp_path / "dispositivos.json"
    estado = tmp_path / "simulador_estado.json"
    catalog.write_text(
        json.dumps(
            {
                "exported_at": "2026-01-01T00:00:00+00:00",
                "dispositivos": [
                    {"id": 1, "numero_serie": "SL-001", "ubicacion": "C1"},
                    {"id": 3, "numero_serie": "SL-003", "ubicacion": "C3"},
                ],
            },
        ),
        encoding="utf-8",
    )
    estado.write_text("{}", encoding="utf-8")

    monkeypatch.setenv("DISPOSITIVOS_FILE", str(catalog))
    monkeypatch.setenv("SIMULADOR_ESTADO_FILE", str(estado))

    from app.core.config import get_settings

    get_settings.cache_clear()

    return catalog, estado


@pytest_asyncio.fixture
async def mock_mongo(monkeypatch):
    client = AsyncMongoMockClient()
    db = client["senselife_data_test"]

    import app.db.mongo as mongo_module

    monkeypatch.setattr(mongo_module, "_client", client)
    monkeypatch.setattr(mongo_module, "get_client", lambda: client)
    monkeypatch.setattr(mongo_module, "get_database", lambda: db)

    await mongo_module.ensure_indexes()
    yield db

    for name in await db.list_collection_names():
        await db[name].delete_many({})

    await mongo_module.close_client()


@pytest_asyncio.fixture
async def client(mock_mongo, simulador_files, monkeypatch):
    async def noop_reanudar() -> None:
        return None

    monkeypatch.setattr(
        "app.services.simulador_service.reanudar_simulaciones_activas",
        noop_reanudar,
    )

    from app.main import app
    import app.services.simulador_service as sim_service

    transport = ASGITransport(app=app)
    async with AsyncClient(transport=transport, base_url="http://test") as ac:
        yield ac

    for task in list(sim_service._simulador_tasks.values()):
        if not task.done():
            task.cancel()
    sim_service._simulador_tasks.clear()


@pytest.fixture
def auth_headers():
    return {"x-internal-token": "test-token"}
