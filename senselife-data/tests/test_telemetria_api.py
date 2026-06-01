from datetime import datetime, timedelta, timezone

import pytest


@pytest.mark.asyncio
async def test_ingest_y_actual(client, auth_headers):
    payload = {
        "id_dispositivo": 101,
        "frecuencia_cardiaca": 140.5,
        "frecuencia_respiratoria": 45.2,
        "tiempo": "2024-05-08T10:15:30+00:00",
    }
    response = await client.post("/api/v1/telemetria/ingest", json=payload, headers=auth_headers)
    assert response.status_code == 201
    body = response.json()
    assert body["id"] >= 1
    assert body["id_dispositivo"] == 101
    assert body["frecuencia_cardiaca"] == 140.5

    actual = await client.get("/api/v1/telemetria/101/actual", headers=auth_headers)
    assert actual.status_code == 200
    assert actual.json()["id"] == body["id"]


@pytest.mark.asyncio
async def test_historial(client, auth_headers):
    now = datetime.now(timezone.utc)
    inicio = (now - timedelta(hours=1)).strftime("%Y-%m-%dT%H:%M:%SZ")
    fin = (now + timedelta(minutes=5)).strftime("%Y-%m-%dT%H:%M:%SZ")

    await client.post(
        "/api/v1/telemetria/ingest",
        json={
            "id_dispositivo": 202,
            "frecuencia_cardiaca": 130,
            "frecuencia_respiratoria": 40,
        },
        headers=auth_headers,
    )

    response = await client.get(
        "/api/v1/telemetria/202",
        params={"fecha_inicio": inicio, "fecha_fin": fin},
        headers=auth_headers,
    )
    assert response.status_code == 200, response.text
    assert len(response.json()) >= 1


@pytest.mark.asyncio
async def test_sin_token_retorna_401(client):
    response = await client.get("/api/v1/telemetria/1/actual")
    assert response.status_code == 401
