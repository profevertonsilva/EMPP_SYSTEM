<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * Output of FiltrationModel::simulate. Penetrations (pen*) are the primary values:
 * efficiencies near 100 % lose precision, so store and sum penetration, and
 * derive efficiency (1 - pen) only for display.
 */
final readonly class SimulationResult
{
    /**
     * @param list<array<string, float|null>> $rows grade-efficiency curve, one row per size class
     *        (d µm, w mass weight, wn number weight, cum cumulative mass, Fs, D, Pe, Cd, nD, R, Cr, nR, Stk, nI, nT, pen, E)
     */
    public function __construct(
        /** overall penetration by mass (EN 1822 mono test: penetration at the MPPS) */
        public float $pen,
        /** overall penetration by number */
        public float $penN,
        /** penetration at the first class ≥ 0.3 µm, null if the range stops below it */
        public ?float $pen03,
        /** quality factor at 0.3 µm, Pa⁻¹ */
        public ?float $QF03,
        /** highest penetration of the curve (at the MPPS) */
        public float $penMin,
        /** lowest fractional efficiency, fraction */
        public float $Emin,
        /** most penetrating particle size, µm */
        public float $mpps,
        public bool $mppsAtEdge,
        /** pressure drop, Pa */
        public float $dP,
        /** viscous (Darcy) share of the pressure drop */
        public float $viscShare,
        /** overall quality factor, Pa⁻¹ */
        public float $QF,
        /** quality factor at the MPPS, Pa⁻¹ */
        public float $QFmpps,
        /** fibre Reynolds number */
        public float $Ref,
        /** Darcy permeability used, m² */
        public float $k1,
        /** continuum Davies permeability, m² */
        public float $k1Davies,
        /** Δp(with slip) / Δp(continuum) */
        public float $slipFactor,
        /** inertial permeability (empirical k1–k2 correlation), m */
        public float $k2,
        /** Kuwabara hydrodynamic factor */
        public float $Ku,
        /** fibre Knudsen number */
        public float $Knf,
        public float $mu,
        public float $rho,
        public float $lam,
        /** packing density 1 - ε */
        public float $alpha,
        /** mass-distribution quantiles, µm */
        public float $d10,
        public float $d50,
        public float $d90,
        /** aerosol mass fraction outside dmin–dmax (left out of the overall efficiency) */
        public float $massOutside,
        public array $rows,
    ) {
    }

    public function efficiency(): float
    {
        return 1 - $this->pen;
    }

    public function efficiencyByNumber(): float
    {
        return 1 - $this->penN;
    }

    /** Scalars only, ready to persist (rows are large: store them separately when needed) */
    public function summary(): array
    {
        $values = get_object_vars($this);
        unset($values['rows']);

        return $values;
    }
}
