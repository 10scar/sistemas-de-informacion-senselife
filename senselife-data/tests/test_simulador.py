import asyncio
import json

import pytest


@pytest.mark.asyncio
async def test_pulso_alto_con_catalogo(client, auth_headers, simulador_files):
    pulso = await client.post("/simulador/1/pulso?modo=alto", follow_redirects=False)
    assert pulso.status_code == 303

    actual = await client.get("/api/v1/telemetria/1/actual", headers=auth_headers)
    assert actual.status_code == 200
    assert actual.json()["frecuencia_cardiaca"] >= 180

    _catalog, estado_path = simulador_files
    estado = json.loads(estado_path.read_text())
    assert estado["1"]["lecturas_generadas"] >= 1


@pytest.mark.asyncio
async def test_toggle_genera_lecturas(client, auth_headers, simulador_files):
    on = await client.post("/simulador/3/toggle", follow_redirects=False)
    assert on.status_code == 303

    await asyncio.sleep(3)

    actual1 = await client.get("/api/v1/telemetria/3/actual", headers=auth_headers)
    assert actual1.status_code == 200
    tiempo1 = actual1.json()["tiempo"]

    off = await client.post("/simulador/3/toggle", follow_redirects=False)
    assert off.status_code == 303

    await asyncio.sleep(2.5)

    actual2 = await client.get("/api/v1/telemetria/3/actual", headers=auth_headers)
    assert actual2.json()["tiempo"] == tiempo1


@pytest.mark.asyncio
async def test_dispositivo_no_en_catalogo_retorna_404(client):
    response = await client.post("/simulador/999/toggle", follow_redirects=False)
    assert response.status_code == 404
