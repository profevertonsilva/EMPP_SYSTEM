<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * EN 1822-1 / ISO 29463 classes by the integral penetration at the MPPS.
 * The class is indicative: the model is a clean, uniform, uncharged medium.
 */
final class En1822
{
    /** @var list<array{cls: string, group: string, minEff: float, maxPen: float}> */
    public const CLASSES = [
        ['cls' => 'E10', 'group' => 'EPA', 'minEff' => 85, 'maxPen' => 0.15],
        ['cls' => 'E11', 'group' => 'EPA', 'minEff' => 95, 'maxPen' => 0.05],
        ['cls' => 'E12', 'group' => 'EPA', 'minEff' => 99.5, 'maxPen' => 0.005],
        ['cls' => 'H13', 'group' => 'HEPA', 'minEff' => 99.95, 'maxPen' => 5e-4],
        ['cls' => 'H14', 'group' => 'HEPA', 'minEff' => 99.995, 'maxPen' => 5e-5],
        ['cls' => 'U15', 'group' => 'ULPA', 'minEff' => 99.9995, 'maxPen' => 5e-6],
        ['cls' => 'U16', 'group' => 'ULPA', 'minEff' => 99.99995, 'maxPen' => 5e-7],
        ['cls' => 'U17', 'group' => 'ULPA', 'minEff' => 99.999995, 'maxPen' => 5e-8],
    ];

    /** Index in CLASSES of the highest class met, -1 below E10 */
    public static function index(float $penAtMpps): int
    {
        $k = -1;
        foreach (self::CLASSES as $i => $c) {
            if ($penAtMpps <= $c['maxPen']) {
                $k = $i;
            }
        }

        return $k;
    }

    /** @return array{cls: string, group: string, minEff: float, maxPen: float}|null null below E10 */
    public static function classify(float $penAtMpps): ?array
    {
        $k = self::index($penAtMpps);

        return $k < 0 ? null : self::CLASSES[$k];
    }

    /**
     * What the next class takes with the same fibres and porosity. Penetration is
     * exponential in thickness, so L_needed = L · ln(P_target) / ln(P_now); Δp scales the same way.
     *
     * @param float      $thickness     current thickness L, µm
     * @param float|null $depositionMin deposition time of L, minutes (null: unknown)
     *
     * @return array{cls: string, group: string, minEff: float, maxPen: float, factor: float,
     *               thickness: float, dP: float, depositionRate: float|null, extraMinutes: int|null}|null
     *         null when already U17 or the penetration is 0 or 1
     */
    public static function nextClass(float $penAtMpps, float $dP, float $thickness, ?float $depositionMin = null): ?array
    {
        $k = self::index($penAtMpps);
        if ($k >= count(self::CLASSES) - 1 || $penAtMpps >= 1 || $penAtMpps <= 0) {
            return null;
        }
        $next = self::CLASSES[$k + 1];
        $factor = log($next['maxPen']) / log($penAtMpps);
        $needed = $thickness * $factor;

        $rate = $depositionMin !== null && $depositionMin > 0 ? $thickness / $depositionMin : null; // µm/min

        return $next + [
            'factor' => $factor,
            'thickness' => $needed,
            'dP' => $dP * $factor,
            'depositionRate' => $rate === null ? null : $rate * 60, // µm/h
            'extraMinutes' => $rate === null ? null : (int) ceil(($needed - $thickness) / $rate),
        ];
    }
}
