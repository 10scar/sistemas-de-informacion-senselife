# senselife-system

Aplicación monolítica de gestión clínica y administrativa para el ecosistema SenseLife. Centraliza la información de centros médicos, dispositivos de monitoreo, pacientes (neonatos), consentimientos, alertas clínicas y la visualización de telemetría en tiempo casi real.

## Descripción del sistema

`senselife-system` es una aplicación web construida con **Laravel 13**, **Livewire 3** y **Tailwind CSS** (tokens de diseño en `resources/css/app.css`). Ofrece dos interfaces principales:

| Módulo | Ruta base | Perfil de acceso |
|--------|-----------|------------------|
| Panel administrativo | `/admin` | Rol Administrador |
| Portal clínico | `/portal` | Roles Médico y Operador de centro |

El sistema persiste datos relacionales en **PostgreSQL** (desarrollo con Laravel Sail). La telemetría de signos vitales (frecuencia cardíaca y respiratoria) se obtiene del microservicio **`senselife-data`** (FastAPI + MongoDB), desacoplado de la base principal.

## Stack tecnológico

- PHP 8.3+
- Laravel 13
- Livewire 3
- PostgreSQL 17 (Sail)
- Redis, Meilisearch, Mailpit (servicios auxiliares en desarrollo)
- Vite (activos frontend)
- Integración HTTP con `senselife-data` para telemetría

## Requisitos

| Componente | Versión recomendada |
|------------|---------------------|
| Docker y Docker Compose | v2 |
| Composer | 2.x |
| Node.js | 20+ (para Vite) |
| Git | — |

Opcional: `senselife-data` en ejecución para telemetría en vivo (puerto 3001).

## Instalación y configuración inicial

### 1. Clonar e instalar dependencias

Desde el directorio `senselife-system`:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configurar base de datos (Sail)

Descomente y configure PostgreSQL en `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

Inicie los contenedores:

```bash
./vendor/bin/sail up -d
```

Ejecute migraciones y datos de prueba:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

### 3. Activos frontend

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

En producción o CI:

```bash
npm run build
```

### 4. Integración con senselife-data

Añada o verifique en `.env`:

```env
TELEMETRIA_DATA_URL=http://host.docker.internal:3001
INTERNAL_API_TOKEN=<secreto compartido con senselife-data>
ALERT_DEDUP_SECONDS=300
```

El valor de `INTERNAL_API_TOKEN` debe ser idéntico a `INTERNAL_TOKEN` en el archivo `.env` de `senselife-data`.

Limpie la caché de configuración tras cambios:

```bash
./vendor/bin/sail artisan config:clear
```

Levante el microservicio de telemetría según su README (`../senselife-data/README.md`).

### 5. Exportar dispositivos al simulador

El simulador de `senselife-data` lee `../senselife-data/data/dispositivos.json`. Para generarlo desde la base de datos:

```bash
./scripts/export-dispositivos-simulador.sh
```

O directamente (con Sail y el volumen `senselife-data/data` montado en `compose.yaml`):

```bash
./vendor/bin/sail artisan telemetria:export-dispositivos
```

Variable en `.env`: `TELEMETRIA_EXPORT_DISPOSITIVOS_PATH` (con Sail: `/var/www/senselife-data-data/dispositivos.json`).

## Variables de entorno relevantes

| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL pública de la aplicación (por defecto: `http://localhost`). |
| `APP_KEY` | Clave de cifrado Laravel (generada con `key:generate`). |
| `DB_*` | Conexión a PostgreSQL. |
| `TELEMETRIA_DATA_URL` | URL base del microservicio de telemetría. |
| `INTERNAL_API_TOKEN` | Token para API interna y comunicación con `senselife-data`. |
| `ALERT_DEDUP_SECONDS` | Segundos de ventana para evitar alertas duplicadas del mismo tipo. |
| `TELEMETRIA_EXPORT_DISPOSITIVOS_PATH` | Ruta del JSON de catálogo para el simulador (por defecto `../senselife-data/data/dispositivos.json`). |
| `VITE_*` | Configuración del bundler frontend. |

Referencia completa: archivo `.env.example`.

## API interna

Rutas expuestas para integración con `senselife-data` y servicios internos. Requieren el encabezado `x-internal-token` con el valor de `INTERNAL_API_TOKEN`.

| Método | Ruta | Descripción |
|--------|------|-------------|
| `POST` | `/api/v1/alertas` | Crea una alerta clínica (`id_paciente` UUID, `id_telemetria`, `estado`, `tipo`: `critico` \| `alerta`). |
| `GET` | `/api/v1/dispositivos/{dispositivo}/contexto` | Devuelve el paciente asociado activamente al dispositivo, si existe. |

Documentación Bruno: `../Documentacion/api/`.

## Funcionalidades principales

### Panel administrativo (`/admin`)

- Gestión de centros médicos y personal.
- Inventario y asignación de dispositivos de hardware.
- Acceso restringido al rol Administrador.

### Portal clínico (`/portal`)

- Listado y registro de pacientes por centro médico.
- Vista individual de paciente con:
  - Promedios de FC/FR (24 horas) desde `senselife-data`.
  - Monitor en tiempo casi real (polling Livewire cada 3 segundos).
  - Últimas alertas registradas en PostgreSQL.
- Asociación opcional de dispositivo al crear paciente.

### Modelo de alertas

Tabla `alertas`: vínculo con paciente (UUID), referencia a lectura de telemetría (`id_telemetria`), estado (`pendiente`, `vista`, `cerrada`) y tipo (`critico`, `alerta`). El estado visual de las tarjetas de paciente deriva de la alerta activa.

## Desarrollo local

### Comandos habituales (Sail)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail npm run dev
./vendor/bin/sail artisan test
```

### URLs de desarrollo

| Recurso | URL |
|---------|-----|
| Aplicación | http://localhost |
| Portal (login) | http://localhost/portal/login |
| Admin (login) | http://localhost/admin/login |
| Vite (HMR) | http://localhost:5173 |
| Mailpit | http://localhost:8025 |

### Pruebas automatizadas

```bash
./vendor/bin/sail artisan test
```

Filtros de ejemplo:

```bash
./vendor/bin/sail artisan test --filter=AlertaStore
./vendor/bin/sail artisan test --filter=PacienteShow
```

## Despliegue

### Desarrollo

Utilice **Laravel Sail** (`compose.yaml`) con el perfil por defecto (PostgreSQL, Redis, Meilisearch, Mailpit).

### Producción

El repositorio incluye artefactos para despliegue en contenedor:

- `Dockerfile`: imagen PHP-FPM de la aplicación.
- `docker-compose.prod.yml`: orquestación con Nginx y perfiles `rds` / `local-db`.
- `docker/nginx/default.conf`: configuración del servidor web.
- `terraform/`: infraestructura AWS (VPC, EC2, RDS) — ver documentación en ese directorio.

Pasos generales de despliegue productivo:

1. Construir la imagen: `docker build -t senselife-app:latest .`
2. Configurar `.env` de producción (base de datos, `APP_KEY`, URLs, tokens de telemetría).
3. Ejecutar migraciones: `php artisan migrate --force`
4. Compilar activos: `npm ci && npm run build`
5. Levantar stack: `docker compose -f docker-compose.prod.yml --profile <perfil> up -d`
6. Desplegar `senselife-data` en la misma red privada y apuntar `TELEMETRIA_DATA_URL` a su endpoint interno.

Variables de ejemplo para producción en AWS: `.env.aws.example`.

## Estructura del proyecto (referencia)

```
senselife-system/
├── app/
│   ├── Http/Controllers/     # Controladores web y API interna
│   ├── Livewire/             # Componentes Admin y Portal
│   ├── Models/               # Dominio: Paciente, Dispositivo, Alerta, etc.
│   └── Services/Telemetria/  # Cliente HTTP hacia senselife-data
├── database/migrations/      # Esquema relacional
├── database/seeders/         # Datos iniciales
├── resources/views/          # Plantillas Blade y Livewire
├── routes/
│   ├── admin.php
│   ├── portal.php
│   └── api.php
├── docker/                   # Nginx y configuración de contenedor
├── terraform/                # Infraestructura como código (AWS)
└── compose.yaml              # Laravel Sail (desarrollo)
```

## Documentación complementaria

| Recurso | Ubicación |
|---------|-----------|
| API entre servicios (Bruno) | `../Documentacion/api/` |
| Microservicio de telemetría | `../senselife-data/README.md` |
| Vista HTML de referencia API | `../Documentacion/index.html` |

## Seguridad

- No commitear archivos `.env` con credenciales reales.
- Rotar `INTERNAL_API_TOKEN` en entornos productivos.
- Restringir las rutas `/api/v1/*` a red interna o servicios autorizados.
- Mantener `APP_DEBUG=false` en producción.

## Licencia

Uso interno del proyecto SenseLife. Consulte el repositorio raíz para términos de licencia aplicables.
