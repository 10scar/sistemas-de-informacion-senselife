from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.db.mongo import close_client, ensure_indexes
from app.routers import health, simulador, telemetria
from app.services.simulador_service import reanudar_simulaciones_activas


@asynccontextmanager
async def lifespan(_: FastAPI):
    await ensure_indexes()
    await reanudar_simulaciones_activas()
    yield
    from app.services.simulador_service import _simulador_tasks

    for task in list(_simulador_tasks.values()):
        if not task.done():
            task.cancel()
    await close_client()


app = FastAPI(
    title="Senselife Data",
    description="Microservicio de telemetría (series de tiempo)",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(health.router)
app.include_router(telemetria.router)
app.include_router(simulador.router)
