#!/usr/bin/env python3
"""
Obsoleto: use el comando Artisan en senselife-system.

    cd senselife-system
    ./vendor/bin/sail artisan telemetria:export-dispositivos

O el script:

    ./scripts/export-dispositivos-simulador.sh
"""

from __future__ import annotations

import sys


def main() -> int:
    print(__doc__, file=sys.stderr)
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
