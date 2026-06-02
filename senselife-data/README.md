# senselife-data

Microservicio de telemetría clínica para el ecosistema SenseLife. Persiste y expone lecturas de frecuencia cardíaca y frecuencia respiratoria provenientes de dispositivos de monitoreo, y coordina la generación de alertas en el sistema principal cuando los valores superan umbrales configurables.

## Descripción del servicio

`senselife-data` es una aplicación **FastAPI** respaldada por **MongoDB** (base de datos no relacional orientada a series de tiempo). Su responsabilidad es:

- Recibir y almacenar lecturas en la colección `telemetria_frecuencia`.
- Consultar historial y última lectura por dispositivo.
- Evaluar umbrales clínicos tras cada ingesta y, cuando corresponde, registrar alertas en `senselife-system` mediante API interna.
- Ofrecer un **simulador web** de desarrollo en la ruta raíz (`/`) para generar telemetría sin hardware físico.

El identificador `id_dispositivo` en este servicio corresponde al campo `id` (entero) de la tabla `dispositivos` en PostgreSQL (`senselife-system`). Las alertas referencian pacientes mediante UUID.

## Arquitectura e integración

```
Dispositivo / Simulador
        |
        v
  POST /api/v1/telemetria/ingest
        |
        v
     MongoDB (telemetria_frecuencia)
        |
        +--> GET historial / actual  <-- senselife-system (portal clínico)
        |
        +--> Si umbral excedido:
                 GET senselife-system /api/v1/dispositivos/{id}/contexto
                 POST senselife-system /api/v1/alertas
```

La documentación de contratos HTTP compartidos entre servicios se encuentra en `../Documentacion/api/` (colección Bruno).

## Requisitos

| Entorno | Requisitos |
|---------|------------|
| Despliegue con Docker | Docker 24+ y Docker Compose v2 |
| Desarrollo local (opcional) | Python 3.12+, pip, MongoDB accesible |

Puerto por defecto del API: **3001**.

## Estructura del proyecto

```
senselife-data/
├── app/
│   ├── main.py              # Aplicación FastAPI y ciclo de vida
│   ├── core/                # Configuración y seguridad (token interno)
│   ├── db/                  # Cliente MongoDB e índices
│   ├── models/              # Esquemas Pydantic
│   ├── routers/             # Telemetría, simulador, health
│   ├── services/            # Lógica de negocio y alertas
│   └── templates/           # Interfaz del simulador (Jinja2)
├── tests/                   # Pruebas automatizadas (pytest)
├── docker-compose.yml
├── Dockerfile
├── requirements.txt
└── .env.example
```

## Configuración

Copie el archivo de ejemplo y ajuste los valores según el entorno:

```bash
cp .env.example .env
```

### Variables de entorno

| Variable | Obligatoria | Descripción |
|----------|-------------|-------------|
| `MONGODB_URI` | Sí | Cadena de conexión a MongoDB. En Docker Compose use `mongodb://mongo:27017`. |
| `MONGODB_DATABASE` | Sí | Nombre de la base de datos (por defecto: `senselife_data`). |
| `INTERNAL_TOKEN` | Sí | Secreto compartido. Debe coincidir con `INTERNAL_API_TOKEN` en `senselife-system`. Se envía en el encabezado `x-internal-token`. |
| `SENSELIFE_SYSTEM_URL` | Sí | URL base del monolito Laravel. Desde el contenedor Docker: `http://host.docker.internal`. En red interna de producción: URL del balanceador o servicio Laravel. |
| `ALERT_DEDUP_SECONDS` | No | Ventana de deduplicación de alertas hacia Laravel (por defecto: 300). |
| `FC_CRITICO_ALTO`, `FC_ALERTA_ALTO`, `FC_ALERTA_BAJO`, `FC_CRITICO_BAJO` | No | Umbrales de frecuencia cardíaca (lpm). |
| `FR_CRITICO_ALTO`, `FR_ALERTA_ALTO`, `FR_ALERTA_BAJO`, `FR_CRITICO_BAJO` | No | Umbrales de frecuencia respiratoria (rpm). |
| `SIMULADOR_INTERVALO_SEG` | No | Intervalo entre lecturas automáticas del simulador (por defecto: 2). |

### Colecciones MongoDB

**`telemetria_frecuencia`**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | entero | Identificador secuencial de la lectura |
| `id_dispositivo` | entero | ID del dispositivo en PostgreSQL |
| `frecuencia_cardiaca` | float | Frecuencia cardíaca (lpm) |
| `frecuencia_respiratoria` | float | Frecuencia respiratoria (rpm) |
| `tiempo` | datetime (UTC) | Marca temporal de la lectura |

**Archivos en `data/`** (no MongoDB):

| Archivo | Origen | Contenido |
|---------|--------|-----------|
| `data/dispositivos.json` | Exportado por `senselife-system` (`telemetria:export-dispositivos`) | Catálogo de dispositivos |
| `data/simulador_estado.json` | Actualizado por el simulador | Activación, contadores, última FC/FR |

## Despliegue

### Despliegue local o de desarrollo (Docker Compose)

Desde el directorio `senselife-data`:

```bash
docker compose up --build -d
```

Verificación:

```bash
curl http://localhost:3001/health
```

Respuesta esperada: `{"status":"ok","mongodb":true}`.

Servicios expuestos:

| Recurso | URL |
|---------|-----|
| API REST | http://localhost:3001 |
| Simulador | http://localhost:3001/ |
| Comprobación de salud | http://localhost:3001/health |
| MongoDB | localhost:27017 |

Detener los contenedores:

```bash
docker compose down
```

### Despliegue en producción

Para entornos productivos se recomienda:

1. Ejecutar la imagen `api` en un orquestador o instancia dedicada con variables de entorno inyectadas de forma segura (no commitear `.env`).
2. Utilizar un clúster MongoDB gestionado (réplicas, respaldos, autenticación).
3. Configurar `SENSELIFE_SYSTEM_URL` con la URL interna al servicio Laravel (red privada o VPC).
4. Restringir el acceso al puerto 3001 mediante firewall o red interna; exponer públicamente solo si existe un API Gateway con autenticación.
5. Deshabilitar o proteger la ruta del simulador (`/`) si no es necesaria en producción.

La construcción de imagen utiliza el `Dockerfile` incluido (Python 3.12-slim, Uvicorn en el puerto 3001).

## API REST

Todas las rutas bajo `/api/v1/telemetria` requieren el encabezado:

```
x-internal-token: <INTERNAL_TOKEN>
```

| Método | Ruta | Descripción |
|--------|------|-------------|
| `POST` | `/api/v1/telemetria/ingest` | Registra una lectura. Cuerpo JSON: `id_dispositivo`, `frecuencia_cardiaca`, `frecuencia_respiratoria`, `tiempo` (ISO 8601, opcional). |
| `GET` | `/api/v1/telemetria/{id_dispositivo}/actual` | Última lectura del dispositivo. |
| `GET` | `/api/v1/telemetria/{id_dispositivo}` | Historial entre `fecha_inicio` y `fecha_fin` (query, ISO 8601). |

Ejemplo de ingesta:

```bash
curl -X POST http://localhost:3001/api/v1/telemetria/ingest \
  -H "Content-Type: application/json" \
  -H "x-internal-token: <token>" \
  -d '{
    "id_dispositivo": 1,
    "frecuencia_cardiaca": 140.5,
    "frecuencia_respiratoria": 45.2,
    "tiempo": "2024-05-08T10:15:30Z"
  }'
```

## Simulador de telemetría

Interfaz web en http://localhost:3001/ que lee el catálogo desde `data/dispositivos.json` y persiste solo el estado de simulación en `data/simulador_estado.json`.

1. En `senselife-system`, exportar dispositivos:

   ```bash
   ./vendor/bin/sail artisan telemetria:export-dispositivos
   ```

2. Abrir el simulador y usar **Activar** / **Desactivar**, **Freq. normales**, **Freq. bajas** o **Freq. altas**.

Variables opcionales: `DISPOSITIVOS_FILE`, `SIMULADOR_ESTADO_FILE` (rutas relativas al proyecto).

### Simulador y rangos vitales neonatales

Referencias clínicas de referencia: PALS/AHA, RCH Melbourne (rangos pediátricos), NRP (AHA 2020/2025).

**Rango normal en recién nacido (referencia)**

| Signo | Rango habitual |
|-------|----------------|
| Frecuencia cardíaca | ~120–170 lpm (vigilia); rango amplio 85–205 lpm (0–3 meses) |
| Frecuencia respiratoria | 30–60 rpm |

**Umbrales de alerta del sistema** (variables `FC_*` / `FR_*` en `.env`; sin cambio por defecto):

| Tipo | FC (lpm) | FR (rpm) |
|------|----------|----------|
| Alerta | &lt;100 / &gt;160 | &lt;25 / &gt;60 |
| Crítico | &lt;80 / &gt;180 | &lt;20 / &gt;70 |

Coherentes con NRP (FC &lt;100 → ventilación; taquipnea neonatal ≥60 rpm según WHO).

**Modos del simulador** (definidos en `app/services/simulador_service.py`):

| Modo | FC (lpm) | FR (rpm) | Efecto esperado |
|------|----------|----------|-----------------|
| **Normal** | 120–160 | 35–55 | Sin alertas |
| **Bajo** | 70–85 | 18–24 | Alerta y/o crítico por bradicardia/bradipnea |
| **Alto** | 175–195 | 62–72 | Alerta y/o crítico por taquicardia/taquipnea |

Use **Freq. normales** para volver al modo normal tras probar frecuencias bajas o altas; si la simulación está activa, el loop automático seguirá generando lecturas del modo seleccionado.

Si el simulador muestra «Sin exportar aún» pero el JSON existe en `data/`, el contenedor `api` puede haberse creado antes del volumen `./data`. Recréelo:

```bash
docker compose up -d --force-recreate api
```

## Integración con senselife-system

En el archivo `.env` de `senselife-system`:

```env
TELEMETRIA_DATA_URL=http://host.docker.internal:3001
INTERNAL_API_TOKEN=<mismo valor que INTERNAL_TOKEN>
```

Cuando Laravel se ejecuta con Sail, `host.docker.internal` permite al contenedor PHP alcanzar este servicio en el host. En Linux, Docker Compose ya incluye `extra_hosts` para el servicio `api`.

Tras modificar variables en Laravel:

```bash
./vendor/bin/sail artisan config:clear
```

## Pruebas automatizadas

Con Docker (recomendado):

```bash
make test
```

Equivalente:

```bash
docker compose run --rm \
  -e INTERNAL_TOKEN=test-token \
  -e MONGODB_DATABASE=senselife_data_test \
  -e SENSELIFE_SYSTEM_URL=http://laravel.test \
  api pytest -q
```

Con entorno virtual local:

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
pytest -q
```

## Documentación de API

Contratos y ejemplos Bruno: `../Documentacion/api/`

| Archivo | Descripción |
|---------|-------------|
| `01-obtener-historial-telemetria.bru` | Historial por rango de fechas |
| `02-obtener-ultima-telemetria.bru` | Última lectura |
| `05-ingesta-telemetria-data.bru` | Ingesta directa en este servicio |
| `03-registrar-alerta-monolito.bru` | Registro de alerta en Laravel |
| `06-dispositivo-contexto.bru` | Contexto dispositivo-paciente |

Entorno local Bruno: `Documentacion/api/environments/Local.bru` (`data_ms_url`: http://localhost:3001).

## Licencia

Uso interno del proyecto SenseLife. Consulte el repositorio raíz para términos de licencia aplicables.
