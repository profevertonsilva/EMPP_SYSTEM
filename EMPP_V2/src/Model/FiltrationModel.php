<?php

declare(strict_types=1);

namespace Empp\Model;

use InvalidArgumentException;

/**
 * Filtration of a clean fibrous membrane by single-fibre theory, model v2.1.
 * Port of emppSimulate() in reference/03_modelo_referencia.js; tests/fixtures holds
 * the reference outputs this class must reproduce.
 *
 * Changes from the spreadsheet (SimulationOptions::legacy reproduces it):
 *  M1  π in the penetration exponent: P = exp(-4·α·ηT·L / (π·(1-α)·df))      [Payet et al. 1992, eq. 1-2]
 *  M2  diffusion with Payet's C'd factor, bounded below 1: ηD = x/(1+x)      [Payet et al. 1992, eq. 11-12]
 *  M3  slip at the fibre in the pressure drop: k1 = k1(Davies) / slip factor [Davies 1973; Pich 1966 / Brown 1993]
 *  M4  lognormal aerosol by CMD and GSD, MMD = CMD·exp(3·ln²GSD) (Hatch-Choate); class weight = difference of the
 *      cumulative distribution between class limits; overall efficiency by mass and by number
 *  M5  d10, d50, d90 read from the cumulative mass distribution
 *  M6  Darcy pressure drop (linear in velocity); the inertial term only as an option
 */
final class FiltrationModel
{
    public const VERSION = '2.1';

    private const BOLTZMANN = 1.38065e-23;

    public function simulate(SimulationInput $i, SimulationOptions $opts = new SimulationOptions()): SimulationResult
    {
        $legacy = $opts->legacy;
        if (!$legacy && !$i->hasAerosol()) {
            throw new InvalidArgumentException('The v2 model needs the aerosol: gsd and cmd (or mmd)');
        }

        $air = Air::at($i->T, $i->PmmHg);
        $T = $air->T;
        $mu = $air->mu;
        $rho = $air->rho;
        $lam = $air->lam;

        $df = $i->df * 1e-6;
        $L = $i->L * 1e-6;
        $e = $i->eps;
        $a = 1 - $e;

        $Ku = -log($a) / 2 - 0.75 + $a - $a * $a / 4;
        $Knf = 2 * $lam / $df;
        $k1Davies = $df * $df / (64 * $a ** 1.5 * (1 + 56 * $a * $a * $a));
        $slip = (1 + 1.996 * $Knf) * $Ku / ($Ku + 1.996 * $Knf * (-0.5 * log($a) - 0.25 + 0.25 * $a * $a));
        $k1 = $legacy ? $k1Davies : $k1Davies / $slip;
        $k2 = exp(-1.71588 * $k1Davies ** -0.08093);

        [$rows, $outside] = $legacy ? $this->rosinRammlerRows($i) : $this->lognormalRows($i);

        $geo = 4 * $a / (1 - $a) * $L / $df / ($legacy ? 1 : M_PI);
        $pen = 0.0;
        $penN = 0.0;
        $Emin = 2.0;
        $mpps = 0.0;
        $mi = 0;
        foreach ($rows as $idx => &$r) {
            $dp = $r['d'] * 1e-6;
            $r['Fs'] = 1 + $lam / $dp * (2.33 + 0.966 * exp(-0.4985 * $dp / $lam));
            $r['D'] = self::BOLTZMANN * $T * $r['Fs'] / (3 * M_PI * $mu * $dp);
            $r['Pe'] = $df * $i->v / $r['D'];
            $r['Cd'] = 1 + 0.388 * $Knf * self::cbrt($e * $r['Pe'] / $Ku);
            $x = 1.6 * self::cbrt($e / $Ku) * $r['Pe'] ** (-2 / 3) * $r['Cd'];
            $r['nD'] = $legacy ? $x : $x / (1 + $x);
            $r['R'] = $dp / $df;
            $r['Cr'] = 1 + 1.996 * $Knf / $r['R'];
            $r['nR'] = min(1.0, 0.6 * ($e / $Ku) * $r['R'] * $r['R'] / (1 + $r['R']) * $r['Cr']);
            $r['Stk'] = $i->rhop * $i->v * $r['Fs'] * $dp * $dp / (9 * $mu * $df);
            $r['nI'] = $r['Stk'] ** 3 / ($r['Stk'] ** 3 + 0.77 * $r['Stk'] * $r['Stk'] + 0.22);
            $r['nT'] = 1 - (1 - $r['nD']) * (1 - $r['nR']) * (1 - $r['nI']);
            $r['pen'] = exp(-$geo * $r['nT']);
            $r['E'] = 1 - $r['pen'];

            $pen += $r['pen'] * $r['w'];
            $penN += $r['pen'] * ($legacy ? $r['w'] : $r['wn']);
            if ($r['E'] < $Emin) {
                $Emin = $r['E'];
                $mpps = $r['d'];
                $mi = $idx;
            }
        }
        unset($r);

        $useInert = $legacy || $opts->inertial;
        $dPv = $mu / $k1 * $i->v * $L;
        $dPi = $useInert ? $rho / $k2 * $i->v * $i->v * $L : 0.0;
        $dP = $dPv + $dPi;

        if ($i->mono) {
            $pen = $rows[$mi]['pen'];
            $penN = $pen;
        }

        $p03 = null;
        foreach ($rows as $r) {
            if ($r['d'] >= 0.3) {
                $p03 = $r['pen'];
                break;
            }
        }

        $qf = static fn (float $p): float => -log(max($p, 1e-300)) / $dP;

        return new SimulationResult(
            pen: $pen,
            penN: $penN,
            pen03: $p03,
            QF03: $p03 === null ? null : $qf($p03),
            penMin: $rows[$mi]['pen'],
            Emin: $Emin,
            mpps: $mpps,
            mppsAtEdge: $mi === 0 || $mi === count($rows) - 1,
            dP: $dP,
            viscShare: $dPv / $dP,
            QF: $qf($pen),
            QFmpps: $qf($rows[$mi]['pen']),
            Ref: $rho * $i->v * $df / $mu,
            k1: $k1,
            k1Davies: $k1Davies,
            slipFactor: $slip,
            k2: $k2,
            Ku: $Ku,
            Knf: $Knf,
            mu: $mu,
            rho: $rho,
            lam: $lam,
            alpha: $a,
            d10: self::quantile($rows, 0.1),
            d50: self::quantile($rows, 0.5),
            d90: self::quantile($rows, 0.9),
            massOutside: $outside,
            rows: $rows,
        );
    }

    /**
     * v2 (M4): n classes with log-spaced limits, weights from the lognormal cumulative
     * distribution by mass (MMD) and by number (CMD).
     *
     * @return array{0: list<array<string, float|null>>, 1: float} rows and mass fraction outside the range
     */
    private function lognormalRows(SimulationInput $i): array
    {
        $n = $i->classes;
        $lo = log($i->dmin);
        $hi = log($i->dmax);
        $lg = log((float) $i->gsd);
        $mmd = $i->mmd ?? $i->cmd * exp(3 * $lg * $lg);
        $cmd = $i->cmd ?? $mmd * exp(-3 * $lg * $lg);
        $F = static fn (float $d, float $median): float => 0.5 * (1 + self::erf(log($d / $median) / (M_SQRT2 * $lg)));

        $rows = [];
        $sum = 0.0;
        $sumN = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $x0 = $lo + $j * ($hi - $lo) / $n;
            $x1 = $lo + ($j + 1) * ($hi - $lo) / $n;
            $w = $F(exp($x1), $mmd) - $F(exp($x0), $mmd);
            $wn = $F(exp($x1), $cmd) - $F(exp($x0), $cmd);
            $rows[] = ['d' => exp(($x0 + $x1) / 2), 'w' => $w, 'wn' => $wn];
            $sum += $w;
            $sumN += $wn;
        }

        $cum = 0.0;
        foreach ($rows as &$r) {
            $r['w'] /= $sum;
            $r['wn'] /= $sumN;
            $cum += $r['w'];
            $r['cum'] = $cum;
        }
        unset($r);

        return [$rows, 1 - $sum];
    }

    /**
     * Legacy: Rosin-Rammler set by the size range, sampled at n + 1 points with constant Δd
     * weights, exactly as in the spreadsheet (the last point is normalised by the sum of the first n).
     *
     * @return array{0: list<array<string, float|null>>, 1: float}
     */
    private function rosinRammlerRows(SimulationInput $i): array
    {
        $n = $i->classes;
        $lo = log($i->dmin);
        $hi = log($i->dmax);
        $arr = log(1e8 / 1.00000001) / log($i->dmax / $i->dmin);
        $dm = $i->dmax / log(1e8) ** (1 / $arr);

        $rows = [];
        $sum = 0.0;
        for ($j = 0; $j <= $n; $j++) {
            $d = exp($lo + $j * ($hi - $lo) / $n);
            $w = $arr / $dm * ($d / $dm) ** ($arr - 1) * exp(-(($d / $dm) ** $arr));
            $rows[] = ['d' => $d, 'w' => $w, 'wn' => null];
            if ($j < $n) {
                $sum += $w;
            }
        }

        $cum = 0.0;
        foreach ($rows as &$r) {
            $r['w'] /= $sum;
            $cum += $r['w'];
            $r['cum'] = $cum;
        }
        unset($r);

        return [$rows, 0.0];
    }

    /** First class whose cumulative mass reaches p (last class if none does) */
    private static function quantile(array $rows, float $p): float
    {
        foreach ($rows as $r) {
            if ($r['cum'] >= $p) {
                return $r['d'];
            }
        }

        return $rows[count($rows) - 1]['d'];
    }

    /** Abramowitz & Stegun 7.1.26, the same approximation as the reference model (|error| < 1.5e-7) */
    public static function erf(float $x): float
    {
        $s = $x < 0 ? -1 : 1;
        $x = abs($x);
        $t = 1 / (1 + 0.3275911 * $x);

        return $s * (1 - ((((1.061405429 * $t - 1.453152027) * $t + 1.421413741) * $t - 0.284496736) * $t + 0.254829592) * $t * exp(-$x * $x));
    }

    private static function cbrt(float $x): float
    {
        return $x < 0 ? -((-$x) ** (1 / 3)) : $x ** (1 / 3);
    }
}
