<?php

declare(strict_types=1);

namespace Empp\Model;

final readonly class SimulationOptions
{
    public function __construct(
        /**
         * Reproduce the spreadsheet "Filter simulation - Fiber mat - Rachel-Everton - 2025.xlsx":
         * no π in the penetration exponent, unbounded diffusion, no slip in the pressure drop,
         * Rosin-Rammler distribution set by the range, inertial term always on. For checking only.
         */
        public bool $legacy = false,
        /** Add the estimated Forchheimer (inertial) term to the Darcy pressure drop */
        public bool $inertial = false,
    ) {
    }

    /** @return array{legacy: bool, inertial: bool} */
    public function toArray(): array
    {
        return ['legacy' => $this->legacy, 'inertial' => $this->inertial];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            legacy: (bool) ($data['legacy'] ?? false),
            inertial: (bool) ($data['inertial'] ?? false),
        );
    }
}
