<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * Test scenarios and reference bands (instructions, sections 4 and 5).
 * These are the seed values; the application keeps them in a configuration table.
 */
final class Presets
{
    /**
     * Aerosol scenarios, standard test conditions only. v in cm/s (as shown), rhop kg/m³, cmd µm.
     * The grade-efficiency curve always spans 0.01–10 µm, so MPPS and ePM exist in every preset.
     * EN 1822 counts at the MPPS (mono): its cmd/gsd only shape the plotted distribution.
     */
    public const AEROSOL = [
        'en1822' => [
            'name' => 'EN 1822 / ISO 29463 flat medium', 'standard' => 'EN 1822-3, ISO 29463-3', 'aerosol' => 'DEHS',
            'basis' => 'Particle count at the MPPS', 'v' => 5.3, 'rhop' => 912, 'cmd' => 0.2, 'gsd' => 1.5, 'mono' => true,
            'dmin' => 0.01, 'dmax' => 10,
        ],
        'niosh_n' => [
            'name' => 'NIOSH 42 CFR 84, N series', 'standard' => '42 CFR 84.174', 'aerosol' => 'NaCl',
            'basis' => 'Photometer (close to mass)', 'v' => 14.2, 'rhop' => 2165, 'cmd' => 0.075, 'gsd' => 1.86, 'mono' => false,
            'dmin' => 0.01, 'dmax' => 10,
        ],
        'niosh_rp' => [
            'name' => 'NIOSH 42 CFR 84, R and P series', 'standard' => '42 CFR 84.174', 'aerosol' => 'DOP',
            'basis' => 'Photometer (close to mass)', 'v' => 14.2, 'rhop' => 985, 'cmd' => 0.185, 'gsd' => 1.60, 'mono' => false,
            'dmin' => 0.01, 'dmax' => 10,
        ],
    ];

    /** Air conditions: T °C, P mmHg */
    public const AIR = [
        'ribeirao_preto' => ['name' => 'Ribeirão Preto', 'T' => 25, 'P' => 715],
        'sea_level' => ['name' => 'Sea level', 'T' => 25, 'P' => 760],
    ];

    /** Glass-fibre HEPA media at 0.3 µm and 5.3 cm/s (literature), Pa⁻¹ */
    public const QF03_BAND = ['low' => 0.012, 'high' => 0.029];

    /** Whole-respirator pressure-drop limits, Pa: order of magnitude only */
    public const DP_REFERENCES = [
        ['dP' => 70, 'label' => 'FFP2 limit, 30 L/min'],
        ['dP' => 240, 'label' => 'FFP2 limit, 95 L/min'],
        ['dP' => 343, 'label' => 'N95 limit, 85 L/min'],
    ];

    /**
     * Model input for a membrane structure under a preset.
     *
     * @param float $df  mean fibre diameter, µm
     * @param float $L   thickness, µm
     * @param float $eps porosity, fraction
     */
    public static function input(string $aerosol, string $air, float $df, float $L, float $eps, int $classes = 100): SimulationInput
    {
        $p = self::AEROSOL[$aerosol] ?? throw new \InvalidArgumentException("Unknown aerosol preset: $aerosol");
        $a = self::AIR[$air] ?? throw new \InvalidArgumentException("Unknown air preset: $air");

        return new SimulationInput(
            T: $a['T'],
            PmmHg: $a['P'],
            df: $df,
            L: $L,
            eps: $eps,
            v: $p['v'] / 100,
            rhop: $p['rhop'],
            dmin: $p['dmin'],
            dmax: $p['dmax'],
            classes: $classes,
            cmd: $p['cmd'],
            gsd: $p['gsd'],
            mono: $p['mono'],
        );
    }
}
