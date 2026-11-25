<?php

declare(strict_types=1);

namespace Empp\Model;

/**
 * Air properties at temperature T (°C) and pressure P (mmHg).
 * Same expressions as the reference model (emppAir): Sutherland viscosity,
 * ideal-gas density and mean free path from viscosity.
 */
final readonly class Air
{
    public function __construct(
        /** absolute temperature, K (T °C + 273, as in the reference model) */
        public float $T,
        /** pressure, Pa */
        public float $P,
        /** dynamic viscosity, Pa·s */
        public float $mu,
        /** density, kg/m³ */
        public float $rho,
        /** mean free path, m */
        public float $lam,
    ) {
    }

    public static function at(float $celsius, float $mmHg): self
    {
        $T = $celsius + 273;
        $P = $mmHg / 760 * 101325;
        $mu = 1.73e-5 * ($T / 273) ** 1.5 * (398 / ($celsius + 398));

        return new self(
            T: $T,
            P: $P,
            mu: $mu,
            rho: $P * 0.028965 / (8.314 * $T),
            lam: 21.2255 * $mu * sqrt($T) / $P,
        );
    }
}
