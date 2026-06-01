from fastapi import APIRouter, HTTPException, Query, Request, status
from fastapi.responses import HTMLResponse, RedirectResponse
from fastapi.templating import Jinja2Templates

from app.services.simulador_service import (
    catalog_metadata,
    listar_dispositivos_sim,
    pulso_simulacion,
    toggle_simulacion,
)

router = APIRouter(tags=["simulador"])
templates = Jinja2Templates(directory="app/templates")


@router.get("/", response_class=HTMLResponse)
async def simulador_index(request: Request) -> HTMLResponse:
    meta = catalog_metadata()
    dispositivos = await listar_dispositivos_sim()
    return templates.TemplateResponse(
        request,
        "simulador.html",
        {
            "dispositivos": dispositivos,
            "exported_at": meta["exported_at"],
            "catalog_path": meta["catalog_path"],
        },
    )


@router.post("/simulador/{id_dispositivo}/toggle")
async def simulador_toggle(id_dispositivo: int) -> RedirectResponse:
    resultado = await toggle_simulacion(id_dispositivo)
    if resultado is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Dispositivo no encontrado en data/dispositivos.json. Exporte desde senselife-system.",
        )
    return RedirectResponse(url="/", status_code=status.HTTP_303_SEE_OTHER)


@router.post("/simulador/{id_dispositivo}/pulso")
async def simulador_pulso(
    id_dispositivo: int,
    modo: str = Query("normal", pattern="^(normal|bajo|alto)$"),
) -> RedirectResponse:
    resultado = await pulso_simulacion(id_dispositivo, modo)
    if resultado is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Dispositivo no encontrado en data/dispositivos.json.",
        )
    return RedirectResponse(url="/", status_code=status.HTTP_303_SEE_OTHER)
