from app.db.mongo import get_database

COUNTER_TELEMETRIA = "telemetria_frecuencia"


async def next_telemetria_id() -> int:
    db = get_database()
    result = await db.counters.find_one_and_update(
        {"_id": COUNTER_TELEMETRIA},
        {"$inc": {"seq": 1}},
        upsert=True,
        return_document=True,
    )
    return int(result["seq"])
