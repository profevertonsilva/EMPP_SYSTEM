<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * ISO 16890-1 ePM efficiencies (port of emppEPM): grade efficiency averaged over the
 * standardised bimodal lognormal volume distributions (urban: ePM1 and ePM2,5; rural: ePM10).
 * Needs a curve covering 0.3–10 µm in classes of constant log width, as FiltrationModel gives.
 * The model has no electrostatic capture, so this is the initial efficiency of the uncharged medium.
 */
final class Iso16890
{
    public const URBAN = ['dA' => 0.3, 'sA' => 2.2, 'dB' => 10, 'sB' => 3.1, 'y' => 0.45];
    public const RURAL = ['dA' => 0.25, 'sA' => 2.2, 'dB' => 11, 'sB' => 4.0, 'y' => 0.18];

    /**
     * @param list<array<string, float|null>> $rows SimulationResult::$rows (uses d and pen)
     *
     * @return array{ePM1: float|null, ePM25: float|null, ePM10: float|null, group: string|null} percentages
     */
    public static function evaluate(array $rows): array
    {
        $o = [
            'ePM1' => self::efficiency($rows, 1, self::URBAN),
            'ePM25' => self::efficiency($rows, 2.5, self::URBAN),
            'ePM10' => self::efficiency($rows, 10, self::RURAL),
        ];
        $down5 = static fn (float $x): float => min(95, floor($x / 5) * 5);

        if ($o['ePM1'] === null || $o['ePM10'] === null) {
            $o['group'] = null;
        } elseif ($o['ePM1'] > 99) {
            $o['group'] = 'Above the scope of ISO 16890 (ePM1 > 99 %)';
        } elseif ($o['ePM1'] >= 50) {
            $o['group'] = 'ISO ePM1 ' . $down5($o['ePM1']) . ' %';
        } elseif ($o['ePM25'] >= 50) {
            $o['group'] = 'ISO ePM2,5 ' . $down5($o['ePM25']) . ' %';
        } elseif ($o['ePM10'] >= 50) {
            $o['group'] = 'ISO ePM10 ' . $down5($o['ePM10']) . ' %';
        } else {
            $o['group'] = 'ISO Coarse';
        }

        return $o;
    }

    /** @param array{dA: float, sA: float, dB: float, sB: float, y: float} $p */
    private static function efficiency(array $rows, float $xmax, array $p): ?float
    {
        $a = 0.0;
        $b = 0.0;
        foreach ($rows as $r) {
            if ($r['d'] >= 0.3 && $r['d'] <= $xmax) {
                $w = $p['y'] * self::lognormal($r['d'], $p['dA'], $p['sA']) + (1 - $p['y']) * self::lognormal($r['d'], $p['dB'], $p['sB']);
                $a += (1 - $r['pen']) * $w;
                $b += $w;
            }
        }

        return $b > 0 ? 100 * $a / $b : null;
    }

    private static function lognormal(float $d, float $d50, float $s): float
    {
        $l = log($s);

        return exp(-(log($d / $d50) ** 2) / (2 * $l * $l)) / ($l * sqrt(2 * M_PI));
    }
}
