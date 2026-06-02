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
async def test_resumen_agrega_stats_y_serie(client, auth_headers):
    now = datetime.now(timezone.utc)
    inicio = (now - timedelta(hours=1)).strftime("%Y-%m-%dT%H:%M:%SZ")
    fin = (now + timedelta(minutes=5)).strftime("%Y-%m-%dT%H:%M:%SZ")

    for fc in (120.0, 130.0, 140.0):
        await client.post(
            "/api/v1/telemetria/ingest",
            json={
                "id_dispositivo": 303,
                "frecuencia_cardiaca": fc,
                "frecuencia_respiratoria": 40,
            },
            headers=auth_headers,
        )

    response = await client.post(
        "/api/v1/telemetria/resumen",
        json={
            "ventanas": [
                {
                    "id_dispositivo": 303,
                    "fecha_inicio": inicio,
                    "fecha_fin": fin,
                },
            ],
            "bucket_segundos": 300,
            "max_puntos": 120,
        },
        headers=auth_headers,
    )
    assert response.status_code == 200, response.text
    body = response.json()
    assert body["stats"]["conteo"] == 3
    assert body["stats"]["min_fc"] == 120.0
    assert body["stats"]["max_fc"] == 140.0
    assert body["stats"]["promedio_fc"] == 130.0
    assert len(body["serie"]) >= 1
    assert len(body["sparkline_fc"]) >= 1


@pytest.mark.asyncio
async def test_resumen_multiples_ventanas_orden_cronologico(client, auth_headers):
    now = datetime.now(timezone.utc)
    ventana_a_fin = (now - timedelta(hours=2)).strftime("%Y-%m-%dT%H:%M:%SZ")
    ventana_a_inicio = (now - timedelta(hours=3)).strftime("%Y-%m-%dT%H:%M:%SZ")
    ventana_b_fin = (now + timedelta(minutes=5)).strftime("%Y-%m-%dT%H:%M:%SZ")
    ventana_b_inicio = (now - timedelta(hours=1)).strftime("%Y-%m-%dT%H:%M:%SZ")

    await client.post(
        "/api/v1/telemetria/ingest",
        json={
            "id_dispositivo": 401,
            "frecuencia_cardiaca": 100,
            "frecuencia_respiratoria": 35,
            "tiempo": ventana_a_inicio,
        },
        headers=auth_headers,
    )
    await client.post(
        "/api/v1/telemetria/ingest",
        json={
            "id_dispositivo": 402,
            "frecuencia_cardiaca": 150,
            "frecuencia_respiratoria": 45,
        },
        headers=auth_headers,
    )

    response = await client.post(
        "/api/v1/telemetria/resumen",
        json={
            "ventanas": [
                {
                    "id_dispositivo": 401,
                    "fecha_inicio": ventana_a_inicio,
                    "fecha_fin": ventana_a_fin,
                },
                {
                    "id_dispositivo": 402,
                    "fecha_inicio": ventana_b_inicio,
                    "fecha_fin": ventana_b_fin,
                },
            ],
            "bucket_segundos": 300,
            "max_puntos": 120,
        },
        headers=auth_headers,
    )
    assert response.status_code == 200, response.text
    body = response.json()
    assert body["stats"]["conteo"] == 2
    assert body["stats"]["min_fc"] == 100.0
    assert body["stats"]["max_fc"] == 150.0
    tiempos = [punto["tiempo"] for punto in body["serie"]]
    assert tiempos == sorted(tiempos)


@pytest.mark.asyncio
async def test_sin_token_retorna_401(client):
    response = await client.get("/api/v1/telemetria/1/actual")
    assert response.status_code == 401
    response = await client.post(
        "/api/v1/telemetria/resumen",
        json={
            "ventanas": [
                {
                    "id_dispositivo": 1,
                    "fecha_inicio": "2024-05-01T00:00:00Z",
                    "fecha_fin": "2024-05-02T00:00:00Z",
                },
            ],
            "bucket_segundos": 300,
        },
    )
    assert response.status_code == 401
