<?php

declare(strict_types=1);

namespace Empp\Model;

use InvalidArgumentException;

/**
 * Input of one filtration simulation, in the units of the reference model.
 * Validation happens here, so the model never runs on values it cannot handle.
 */
final readonly class SimulationInput
{
    public function __construct(
        /** air temperature, °C */
        public float $T,
        /** air pressure, mmHg */
        public float $PmmHg,
        /** mean fibre diameter, µm */
        public float $df,
        /** membrane thickness, µm */
        public float $L,
        /** porosity, fraction 0–1 */
        public float $eps,
        /** face velocity, m/s */
        public float $v,
        /** particle density, kg/m³ */
        public float $rhop,
        /** smallest particle diameter of the grade-efficiency curve, µm */
        public float $dmin,
        /** largest particle diameter of the grade-efficiency curve, µm */
        public float $dmax,
        /** number of log-spaced size classes */
        public int $classes = 100,
        /** count median diameter of the aerosol, µm (as the standards define it) */
        public ?float $cmd = null,
        /** mass median diameter, µm; used instead of cmd when known */
        public ?float $mmd = null,
        /** geometric standard deviation of the aerosol */
        public ?float $gsd = null,
        /** test at the MPPS (EN 1822): overall efficiency = efficiency at the MPPS */
        public bool $mono = false,
    ) {
        self::check($T > -273, 'T must be above -273 °C');
        self::check($PmmHg > 0, 'PmmHg must be positive');
        self::check($df > 0, 'df must be positive');
        self::check($L > 0, 'L must be positive');
        self::check($eps > 0 && $eps < 1, 'eps must be between 0 and 1 (exclusive)');
        self::check($v > 0, 'v must be positive');
        self::check($rhop > 0, 'rhop must be positive');
        self::check($dmin > 0 && $dmax > $dmin, 'the size range needs 0 < dmin < dmax');
        self::check($classes >= 2 && $classes <= 1000, 'classes must be between 2 and 1000');
        self::check($cmd === null || $cmd > 0, 'cmd must be positive');
        self::check($mmd === null || $mmd > 0, 'mmd must be positive');
        self::check($gsd === null || $gsd > 1, 'gsd must be greater than 1');
    }

    /** True when the lognormal aerosol (needed by the v2 model, not by legacy) is fully defined */
    public function hasAerosol(): bool
    {
        return $this->gsd !== null && ($this->cmd !== null || $this->mmd !== null);
    }

    /** @param array<string, mixed> $data keys as in the reference model (T, PmmHg, df, L, eps, v, rhop, ...) */
    public static function fromArray(array $data): self
    {
        foreach (['T', 'PmmHg', 'df', 'L', 'eps', 'v', 'rhop', 'dmin', 'dmax'] as $key) {
            if (!isset($data[$key]) || !is_numeric($data[$key])) {
                throw new InvalidArgumentException("Missing or non-numeric input: $key");
            }
        }
        $optional = static fn (string $k): ?float => isset($data[$k]) && $data[$k] !== '' ? (float) $data[$k] : null;

        return new self(
            T: (float) $data['T'],
            PmmHg: (float) $data['PmmHg'],
            df: (float) $data['df'],
            L: (float) $data['L'],
            eps: (float) $data['eps'],
            v: (float) $data['v'],
            rhop: (float) $data['rhop'],
            dmin: (float) $data['dmin'],
            dmax: (float) $data['dmax'],
            classes: (int) ($data['classes'] ?? 100),
            cmd: $optional('cmd'),
            mmd: $optional('mmd'),
            gsd: $optional('gsd'),
            mono: (bool) ($data['mono'] ?? false),
        );
    }

    /** @return array<string, float|int|bool|null> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /** Same input with some values replaced (what-if, per-image runs) */
    public function with(mixed ...$changes): self
    {
        return new self(...array_merge(get_object_vars($this), $changes));
    }

    private static function check(bool $ok, string $message): void
    {
        if (!$ok) {
            throw new InvalidArgumentException($message);
        }
    }
}
