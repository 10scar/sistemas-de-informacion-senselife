#!/usr/bin/env bash
# Exporta dispositivos de PostgreSQL al catálogo JSON del simulador en senselife-data.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

DEST_HOST="${ROOT}/../senselife-data/data/dispositivos.json"
SAIL_PATH="/var/www/senselife-data-data/dispositivos.json"

run_artisan() {
  if [[ -x ./vendor/bin/sail ]] && docker compose -f compose.yaml ps laravel.test 2>/dev/null | grep -q Up; then
    ./vendor/bin/sail artisan telemetria:export-dispositivos --path="$SAIL_PATH" "$@"
  else
    php artisan telemetria:export-dispositivos --path="$DEST_HOST" "$@"
  fi
}

if [[ $# -gt 0 ]]; then
  if [[ -x ./vendor/bin/sail ]] && docker compose -f compose.yaml ps laravel.test 2>/dev/null | grep -q Up; then
    ./vendor/bin/sail artisan telemetria:export-dispositivos "$@"
  else
    php artisan telemetria:export-dispositivos "$@"
  fi
else
  run_artisan
fi

mkdir -p "$(dirname "$DEST_HOST")"
if [[ -f storage/app/dispositivos-simulador.json ]] && [[ ! -f "$DEST_HOST" || storage/app/dispositivos-simulador.json -nt "$DEST_HOST" ]]; then
  cp -f storage/app/dispositivos-simulador.json "$DEST_HOST" 2>/dev/null || true
fi

if [[ -f "$DEST_HOST" ]]; then
  echo "Catálogo del simulador: $DEST_HOST"
fi
