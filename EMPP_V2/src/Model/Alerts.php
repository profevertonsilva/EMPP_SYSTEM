<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * Validity alerts of a simulation (port of emppAlerts). Always shown with the
 * result and in the report. Texts are in English, as the interface.
 */
final class Alerts
{
    /** @return list<Alert> */
    public static function evaluate(SimulationInput $i, SimulationResult $r, SimulationOptions $opts = new SimulationOptions()): array
    {
        $alerts = [];
        $note = static function (string $code, string $text) use (&$alerts): void {
            $alerts[] = new Alert(AlertLevel::NOTE, $code, $text);
        };
        $check = static function (string $code, string $text) use (&$alerts): void {
            $alerts[] = new Alert(AlertLevel::CHECK, $code, $text);
        };
        $a = 1 - $i->eps;

        if ($opts->legacy) {
            $check('LEGACY', 'Legacy spreadsheet equations in use: no π in the penetration exponent, no slip in the pressure drop, size distribution set by the range. Use only to check against the spreadsheet.');
        }
        if ($a > 0.3 || $a < 0.006) {
            $check('ALPHA', 'Packing density ' . self::fixed($a, 3) . ' is outside the range of the Davies permeability correlation (0.006–0.3).');
        }
        if ($r->Knf > 0.25) {
            $note('SLIP', 'Fibre Knudsen number ' . self::fixed($r->Knf, 2) . ': slip flow at the fibre. Pressure drop is reduced by a slip factor of ' . self::fixed($r->slipFactor, 2) . ' relative to the continuum value.');
        }
        if ($r->Knf > 2) {
            $check('KNF', 'Fibre Knudsen number above 2: transition regime, beyond the range of the slip corrections used.');
        }
        if ($i->L / $i->df < 20) {
            $check('THIN', 'The membrane is only ' . self::fixed($i->L / $i->df, 0) . ' fibre diameters thick. A uniform-bed model is less reliable for so few fibre layers; local thin spots will raise the real penetration.');
        }
        if ($r->mppsAtEdge) {
            $check('EDGE', 'The lowest efficiency is at the edge of the size range, so the true MPPS may lie outside it. Widen the range before reading a class.');
        }
        if ($r->mpps > $i->df) {
            $note('R1', 'The MPPS (' . self::fixed($r->mpps * 1000, 0) . ' nm) is larger than the fibre diameter. The interception correlation was fitted for particles smaller than the fibre and is extrapolated here.');
        }
        if ($r->massOutside > 0.01) {
            $check('MASS', self::fixed($r->massOutside * 100, 1) . ' % of the aerosol mass lies outside the size range and is left out of the overall efficiency.');
        }
        if ($r->penMin < 1e-9) {
            $note('SAT', 'Penetration below 10⁻⁹ at every size: differences at this level are beyond what the model, or a measurement, can resolve.');
        }
        if ($r->viscShare < 0.9) {
            $check('INERT', 'The estimated inertial (Forchheimer) term is ' . self::fixed((1 - $r->viscShare) * 100, 0) . ' % of the pressure drop at a fibre Reynolds number of ' . self::exponential($r->Ref, 1) . '. It comes from an empirical k₁–k₂ correlation extrapolated to nanofibre media. Confirm with Δp measured at several velocities (Validation).');
        }
        if ($r->Ref > 0.5) {
            $check('RE', 'Fibre Reynolds number ' . self::fixed($r->Ref, 2) . ': inertial losses may no longer be negligible and the Darcy pressure drop can underestimate the real one.');
        } elseif ($r->viscShare >= 0.999) {
            $note('DARCY', 'Pressure drop is linear in velocity (Darcy flow with slip). Fibre Reynolds number ' . self::exponential($r->Ref, 1) . ', far below 1.');
        }
        $note('BASIS', 'Clean, uniform, uncharged filter: no electrostatic capture, no dust loading, no particle bounce, one mean fibre diameter. Real membranes with a wide diameter distribution or uneven thickness usually show lower efficiency and lower pressure drop.');

        return $alerts;
    }

    /** @param list<Alert> $alerts */
    public static function countChecks(array $alerts): int
    {
        return count(array_filter($alerts, static fn (Alert $a): bool => $a->level === AlertLevel::CHECK));
    }

    /** Like JavaScript toFixed */
    private static function fixed(float $x, int $decimals): string
    {
        return number_format($x, $decimals, '.', '');
    }

    /** Like JavaScript toExponential: 2.6e-3, 1.2e+0 */
    private static function exponential(float $x, int $decimals): string
    {
        return sprintf('%.' . $decimals . 'e', $x);
    }
}
