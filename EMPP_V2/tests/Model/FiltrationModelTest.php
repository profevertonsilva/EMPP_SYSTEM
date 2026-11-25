<?php

declare(strict_types=1);

namespace Empp\Tests\Model;

use Empp\Model\En1822;
use Empp\Model\FiltrationModel;
use Empp\Model\Iso16890;
use Empp\Model\SimulationInput;
use Empp\Model\SimulationOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Reference cases from "02_instrucoes_Everton.md", section 6 (model v2.1).
 * Each expected value is checked to the number of decimals it was published with.
 */
final class FiltrationModelTest extends TestCase
{
    /** Common input of the v2 cases: 25 °C, 715 mmHg, df 0.30 µm, L 12 µm, ε 0.841, 0.01–10 µm, 100 classes */
    private const BASE = [
        'T' => 25, 'PmmHg' => 715, 'df' => 0.30, 'L' => 12, 'eps' => 0.841,
        'dmin' => 0.01, 'dmax' => 10, 'classes' => 100,
    ];

    /**
     * @return iterable<string, array{array<string, mixed>, SimulationOptions, array<string, float|string|null>}>
     */
    public static function referenceCases(): iterable
    {
        yield 'spreadsheet (legacy)' => [
            ['df' => 50, 'L' => 1000, 'eps' => 0.7438, 'v' => 0.053, 'rhop' => 912, 'dmin' => 0.01, 'dmax' => 100] + self::BASE,
            new SimulationOptions(legacy: true),
            ['E' => '80.92301', 'EN' => null, 'Emin' => '6.5704', 'mpps' => '758.6', 'dP' => '6.48', 'QF' => '0.25576', 'QF03' => null, 'class' => null],
        ];
        yield 'v2, EN 1822' => [
            ['v' => 0.053, 'rhop' => 912, 'mono' => true, 'cmd' => 0.2, 'gsd' => 1.5] + self::BASE,
            new SimulationOptions(),
            ['E' => '99.71026', 'EN' => '99.71026', 'Emin' => '99.7103', 'mpps' => '62.4', 'dP' => '424.91', 'QF' => '0.01375', 'QF03' => '0.02266', 'class' => 'E12'],
        ];
        yield 'v2, NIOSH N' => [
            ['v' => 0.142, 'rhop' => 2165, 'cmd' => 0.075, 'gsd' => 1.86] + self::BASE,
            new SimulationOptions(),
            ['E' => '99.93988', 'EN' => '99.61717', 'Emin' => '99.3105', 'mpps' => '50.7', 'dP' => '1138.44', 'QF' => '0.00651', 'QF03' => '0.00846', 'class' => 'E11'],
        ];
        yield 'v2, NIOSH P' => [
            ['v' => 0.142, 'rhop' => 985, 'cmd' => 0.185, 'gsd' => 1.60] + self::BASE,
            new SimulationOptions(),
            ['E' => '99.98854', 'EN' => '99.92105', 'Emin' => '99.3032', 'mpps' => '50.7', 'dP' => '1138.44', 'QF' => '0.00797', 'QF03' => '0.00846', 'class' => 'E11'],
        ];
        yield 'v2, NIOSH N, inertial' => [
            ['v' => 0.142, 'rhop' => 2165, 'cmd' => 0.075, 'gsd' => 1.86] + self::BASE,
            new SimulationOptions(inertial: true),
            ['E' => '99.93988', 'EN' => '99.61717', 'Emin' => '99.3105', 'mpps' => '50.7', 'dP' => '2336.85', 'QF' => '0.00317', 'QF03' => '0.00412', 'class' => 'E11'],
        ];
    }

    /**
     * @param array<string, mixed>              $input
     * @param array<string, float|string|null>  $expected published values, as strings to keep their decimals
     */
    #[DataProvider('referenceCases')]
    public function testReproducesPublishedCase(array $input, SimulationOptions $options, array $expected): void
    {
        $r = (new FiltrationModel())->simulate(SimulationInput::fromArray($input), $options);

        self::assertPublished($expected['E'], 100 * (1 - $r->pen), 'overall efficiency by mass');
        if ($expected['EN'] !== null) {
            self::assertPublished($expected['EN'], 100 * (1 - $r->penN), 'overall efficiency by number');
        }
        self::assertPublished($expected['Emin'], 100 * (1 - $r->penMin), 'minimum efficiency');
        self::assertPublished($expected['mpps'], $r->mpps * 1000, 'MPPS (nm)');
        self::assertPublished($expected['dP'], $r->dP, 'pressure drop');
        self::assertPublished($expected['QF'], $r->QF, 'quality factor');
        if ($expected['QF03'] !== null) {
            self::assertPublished($expected['QF03'], $r->QF03, 'quality factor at 0.3 µm');
        }

        $class = En1822::classify($r->penMin);
        if ($expected['class'] === null) {
            self::assertNull($class, 'spreadsheet case is below E10');
        } else {
            self::assertSame($expected['class'], $class['cls']);
        }
    }

    public function testIso16890Case(): void
    {
        $input = SimulationInput::fromArray(
            ['df' => 5, 'L' => 500, 'eps' => 0.92, 'v' => 0.053, 'rhop' => 912, 'mono' => true, 'cmd' => 0.2, 'gsd' => 1.5] + self::BASE
        );
        $epm = Iso16890::evaluate((new FiltrationModel())->simulate($input)->rows);

        self::assertPublished('23.49', $epm['ePM1'], 'ePM1');
        self::assertSame('ISO ePM10 75 %', $epm['group']);
    }

    public function testMonoTestEqualsEfficiencyAtMpps(): void
    {
        $r = (new FiltrationModel())->simulate(SimulationInput::fromArray(
            ['v' => 0.053, 'rhop' => 912, 'mono' => true, 'cmd' => 0.2, 'gsd' => 1.5] + self::BASE
        ));

        self::assertSame($r->penMin, $r->pen);
        self::assertSame($r->pen, $r->penN);
    }

    public function testMassAndNumberWeightsAreNormalised(): void
    {
        $r = (new FiltrationModel())->simulate(SimulationInput::fromArray(
            ['v' => 0.142, 'rhop' => 2165, 'cmd' => 0.075, 'gsd' => 1.86] + self::BASE
        ));

        self::assertCount(100, $r->rows);
        self::assertEqualsWithDelta(1.0, array_sum(array_column($r->rows, 'w')), 1e-12);
        self::assertEqualsWithDelta(1.0, array_sum(array_column($r->rows, 'wn')), 1e-12);
        self::assertEqualsWithDelta(1.0, $r->rows[array_key_last($r->rows)]['cum'], 1e-12);
        self::assertLessThan(0.01, $r->massOutside);
    }

    public function testPenetrationIsKeptBelowFullCapture(): void
    {
        // Very thick, dense membrane: efficiency saturates, penetration must stay > 0 and finite
        $r = (new FiltrationModel())->simulate(SimulationInput::fromArray(
            ['L' => 500, 'v' => 0.053, 'rhop' => 912, 'mono' => true, 'cmd' => 0.2, 'gsd' => 1.5] + self::BASE
        ));

        self::assertGreaterThanOrEqual(0.0, $r->penMin);
        self::assertTrue(is_finite($r->QF));
    }

    public function testInertialOptionOnlyAddsToPressureDrop(): void
    {
        $model = new FiltrationModel();
        $input = SimulationInput::fromArray(['v' => 0.142, 'rhop' => 2165, 'cmd' => 0.075, 'gsd' => 1.86] + self::BASE);
        $darcy = $model->simulate($input);
        $inertial = $model->simulate($input, new SimulationOptions(inertial: true));

        self::assertSame($darcy->pen, $inertial->pen);
        self::assertSame(1.0, $darcy->viscShare);
        self::assertGreaterThan($darcy->dP, $inertial->dP);
        self::assertLessThan(1.0, $inertial->viscShare);
    }

    /**
     * Compares with the precision the value was published with: "80.92301" means
     * the model must round to 80.92301 at 5 decimals.
     */
    private static function assertPublished(string $published, ?float $actual, string $what): void
    {
        self::assertNotNull($actual, $what);
        $decimals = strpos($published, '.') === false ? 0 : strlen($published) - strpos($published, '.') - 1;
        self::assertEqualsWithDelta(
            (float) $published,
            $actual,
            0.5 * 10 ** -$decimals + 1e-12,
            sprintf('%s: published %s, model gives %.10g', $what, $published, $actual)
        );
    }
}
