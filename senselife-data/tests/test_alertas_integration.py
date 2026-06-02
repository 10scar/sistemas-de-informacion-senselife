import pytest
import respx
from httpx import Response


@pytest.mark.asyncio
@respx.mock
async def test_ingest_critico_dispara_alerta(client, auth_headers):
    paciente_uuid = "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
    respx.get("http://laravel.test/api/v1/dispositivos/5/contexto").mock(
        return_value=Response(200, json={"id_dispositivo": 5, "id_paciente": paciente_uuid}),
    )
    alert_route = respx.post("http://laravel.test/api/v1/alertas").mock(
        return_value=Response(201, json={"id": 1}),
    )

    response = await client.post(
        "/api/v1/telemetria/ingest",
        json={
            "id_dispositivo": 5,
            "frecuencia_cardiaca": 190,
            "frecuencia_respiratoria": 42,
        },
        headers=auth_headers,
    )
    assert response.status_code == 201
    assert alert_route.called
    request_body = alert_route.calls.last.request.read().decode()
    assert paciente_uuid in request_body
    assert "critico" in request_body
    assert "190" in request_body
    assert "42" in request_body
    assert "frecuencia_cardiaca" in request_body
    assert "frecuencia_respiratoria" in request_body
