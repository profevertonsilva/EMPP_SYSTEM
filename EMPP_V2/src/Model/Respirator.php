<?php

declare(strict_types=1);

namespace Empp\Model;

/** Respirator efficiency levels (efficiency criterion of the filter medium only) */
final class Respirator
{
    /** @var list<array{name: string, std: string, minEff: float}> */
    public const LEVELS = [
        ['name' => 'FFP1', 'std' => 'EN 149', 'minEff' => 80],
        ['name' => 'FFP2', 'std' => 'EN 149', 'minEff' => 94],
        ['name' => 'N95', 'std' => 'NIOSH 42 CFR 84', 'minEff' => 95],
        ['name' => 'FFP3', 'std' => 'EN 149', 'minEff' => 99],
        ['name' => 'N99', 'std' => 'NIOSH 42 CFR 84', 'minEff' => 99],
        ['name' => 'N100', 'std' => 'NIOSH 42 CFR 84', 'minEff' => 99.97],
    ];

    /** @return list<string> names of the levels whose efficiency is met at the MPPS */
    public static function levelsMet(float $penAtMpps): array
    {
        $e = 100 * (1 - $penAtMpps);
        $met = [];
        foreach (self::LEVELS as $level) {
            if ($e >= $level['minEff']) {
                $met[] = $level['name'];
            }
        }

        return $met;
    }
}
