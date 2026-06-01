from fastapi import APIRouter

from app.db.mongo import ping_database

router = APIRouter(tags=["health"])


@router.get("/health")
async def health() -> dict:
    mongo_ok = await ping_database()
    return {
        "status": "ok" if mongo_ok else "degraded",
        "mongodb": mongo_ok,
    }
