<?php

namespace App\Support;

use App\Enums\AlertaCategoriaClinica;
use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Models\Telemetria\Alerta;

class AlertaPresentacion
{
    /**
     * @return array{etiqueta: string, clase: string}
     */
    public static function valorCritico(Alerta $alerta): array
    {
        $fc = $alerta->frecuencia_cardiaca !== null ? (float) $alerta->frecuencia_cardiaca : null;
        $fr = $alerta->frecuencia_respiratoria !== null ? (float) $alerta->frecuencia_respiratoria : null;

        if ($fc === null && $fr === null) {
            return [
                'etiqueta' => __('portal/alertas.valor_no_disponible'),
                'clase' => 'text-neutral-500',
            ];
        }

        $componente = self::componenteDominante($fc, $fr, $alerta->tipo);

        if ($componente === 'fc' && $fc !== null) {
            return [
                'etiqueta' => __('portal/alertas.valor_fc', ['valor' => number_format($fc, 0)]),
                'clase' => $alerta->tipo === AlertaTipo::Critico ? 'text-error font-semibold' : 'text-warning-text font-semibold',
            ];
        }

        if ($componente === 'fr' && $fr !== null) {
            return [
                'etiqueta' => __('portal/alertas.valor_fr', ['valor' => number_format($fr, 0)]),
                'clase' => $alerta->tipo === AlertaTipo::Critico ? 'text-error font-semibold' : 'text-warning-text font-semibold',
            ];
        }

        if ($fc !== null) {
            return [
                'etiqueta' => __('portal/alertas.valor_fc', ['valor' => number_format($fc, 0)]),
                'clase' => 'text-error font-semibold',
            ];
        }

        return [
            'etiqueta' => __('portal/alertas.valor_fr', ['valor' => number_format((float) $fr, 0)]),
            'clase' => 'text-warning-text font-semibold',
        ];
    }

    public static function categoriaClinica(Alerta $alerta): AlertaCategoriaClinica
    {
        $fc = $alerta->frecuencia_cardiaca !== null ? (float) $alerta->frecuencia_cardiaca : null;
        $fr = $alerta->frecuencia_respiratoria !== null ? (float) $alerta->frecuencia_respiratoria : null;

        if ($fc === null && $fr === null) {
            return AlertaCategoriaClinica::Otro;
        }

        $umbrales = config('telemetria.monitor_umbrales');
        $fcUmbrales = $umbrales['fc'] ?? [];
        $frUmbrales = $umbrales['fr'] ?? [];
        $critico = $alerta->tipo === AlertaTipo::Critico;
        $componente = self::componenteDominante($fc, $fr, $alerta->tipo);

        if ($componente === 'fc' && $fc !== null) {
            $alto = (float) ($critico ? ($fcUmbrales['critico_alto'] ?? 180) : ($fcUmbrales['alerta_alto'] ?? 160));
            $bajo = (float) ($critico ? ($fcUmbrales['critico_bajo'] ?? 80) : ($fcUmbrales['alerta_bajo'] ?? 100));

            if ($fc >= $alto) {
                return AlertaCategoriaClinica::Taquicardia;
            }

            if ($fc <= $bajo) {
                return AlertaCategoriaClinica::Bradicardia;
            }
        }

        if ($componente === 'fr' && $fr !== null) {
            $alto = (float) ($critico ? ($frUmbrales['critico_alto'] ?? 70) : ($frUmbrales['alerta_alto'] ?? 60));
            $bajo = (float) ($critico ? ($frUmbrales['critico_bajo'] ?? 20) : ($frUmbrales['alerta_bajo'] ?? 25));

            if ($fr >= $alto) {
                return AlertaCategoriaClinica::Taquipnea;
            }

            if ($fr <= $bajo) {
                return AlertaCategoriaClinica::Bradipnea;
            }
        }

        if ($fc !== null) {
            $alto = (float) ($fcUmbrales['alerta_alto'] ?? 160);
            $bajo = (float) ($fcUmbrales['alerta_bajo'] ?? 100);

            if ($fc >= $alto) {
                return AlertaCategoriaClinica::Taquicardia;
            }

            if ($fc <= $bajo) {
                return AlertaCategoriaClinica::Bradicardia;
            }
        }

        if ($fr !== null) {
            $alto = (float) ($frUmbrales['alerta_alto'] ?? 60);
            $bajo = (float) ($frUmbrales['alerta_bajo'] ?? 25);

            if ($fr >= $alto) {
                return AlertaCategoriaClinica::Taquipnea;
            }

            if ($fr <= $bajo) {
                return AlertaCategoriaClinica::Bradipnea;
            }
        }

        return AlertaCategoriaClinica::Otro;
    }

    /**
     * @return array{label: string, color: string}
     */
    public static function metaCategoria(AlertaCategoriaClinica $categoria): array
    {
        return match ($categoria) {
            AlertaCategoriaClinica::Taquipnea => [
                'label' => __('portal/dashboard.cat_taquipnea'),
                'color' => '#D32F2F',
            ],
            AlertaCategoriaClinica::Bradicardia => [
                'label' => __('portal/dashboard.cat_bradicardia'),
                'color' => '#F57C00',
            ],
            AlertaCategoriaClinica::Bradipnea => [
                'label' => __('portal/dashboard.cat_bradipnea'),
                'color' => '#FBC02D',
            ],
            AlertaCategoriaClinica::Taquicardia => [
                'label' => __('portal/dashboard.cat_taquicardia'),
                'color' => '#B71C1C',
            ],
            AlertaCategoriaClinica::Otro => [
                'label' => __('portal/dashboard.cat_otro'),
                'color' => '#9E9E9E',
            ],
        };
    }

    public static function etiquetaEstado(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => __('portal/alertas.estado_pendiente'),
            AlertaEstado::Vista => __('portal/alertas.estado_revision'),
            AlertaEstado::Atendida => __('portal/alertas.estado_atendido'),
            AlertaEstado::Cerrada => __('portal/alertas.estado_ignorado'),
        };
    }

    public static function claseBadgeEstado(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => 'bg-warning-light text-warning-text border-warning-border',
            AlertaEstado::Vista => 'bg-warning-light text-warning-text border-warning-border',
            AlertaEstado::Atendida => 'bg-success-light text-success-text border-success-border',
            AlertaEstado::Cerrada => 'bg-neutral-100 text-neutral-600 border-neutral-300',
        };
    }

    public static function etiquetaCuna(Alerta $alerta): string
    {
        $dispositivo = $alerta->paciente?->asociacionActiva?->dispositivo;

        if ($dispositivo?->ubicacion) {
            return (string) $dispositivo->ubicacion;
        }

        if ($dispositivo?->numero_serie) {
            return (string) $dispositivo->numero_serie;
        }

        return (string) ($alerta->paciente?->identificador_publico ?? __('portal/alertas.cuna_no_disponible'));
    }

    private static function componenteDominante(?float $fc, ?float $fr, AlertaTipo $tipo): ?string
    {
        $umbrales = config('telemetria.monitor_umbrales');
        $fcUmbrales = $umbrales['fc'] ?? [];
        $frUmbrales = $umbrales['fr'] ?? [];

        $fcFuera = $fc !== null && self::fueraDeRango(
            $fc,
            (float) ($fcUmbrales['alerta_bajo'] ?? 100),
            (float) ($fcUmbrales['alerta_alto'] ?? 160),
            $tipo === AlertaTipo::Critico,
            (float) ($fcUmbrales['critico_bajo'] ?? 80),
            (float) ($fcUmbrales['critico_alto'] ?? 180),
        );

        $frFuera = $fr !== null && self::fueraDeRango(
            $fr,
            (float) ($frUmbrales['alerta_bajo'] ?? 25),
            (float) ($frUmbrales['alerta_alto'] ?? 60),
            $tipo === AlertaTipo::Critico,
            (float) ($frUmbrales['critico_bajo'] ?? 20),
            (float) ($frUmbrales['critico_alto'] ?? 70),
        );

        if ($fcFuera && ! $frFuera) {
            return 'fc';
        }

        if ($frFuera && ! $fcFuera) {
            return 'fr';
        }

        if ($fcFuera && $frFuera && $fc !== null && $fr !== null) {
            $fcDesviacion = self::desviacionRelativa($fc, (float) ($fcUmbrales['alerta_bajo'] ?? 100), (float) ($fcUmbrales['alerta_alto'] ?? 160));
            $frDesviacion = self::desviacionRelativa($fr, (float) ($frUmbrales['alerta_bajo'] ?? 25), (float) ($frUmbrales['alerta_alto'] ?? 60));

            return $fcDesviacion >= $frDesviacion ? 'fc' : 'fr';
        }

        return $fc !== null ? 'fc' : 'fr';
    }

    private static function fueraDeRango(
        float $valor,
        float $alertaBajo,
        float $alertaAlto,
        bool $critico,
        float $criticoBajo,
        float $criticoAlto,
    ): bool {
        if ($critico) {
            return $valor <= $criticoBajo || $valor >= $criticoAlto;
        }

        return $valor <= $alertaBajo || $valor >= $alertaAlto;
    }

    private static function desviacionRelativa(float $valor, float $bajo, float $alto): float
    {
        if ($valor < $bajo) {
            return ($bajo - $valor) / max($bajo, 1);
        }

        if ($valor > $alto) {
            return ($valor - $alto) / max($alto, 1);
        }

        return 0.0;
    }
}
