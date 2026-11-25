<?php

declare(strict_types=1);

namespace Empp\Tests\Model;

use Empp\Model\Alerts;
use Empp\Model\AlertLevel;
use Empp\Model\En1822;
use Empp\Model\FiltrationModel;
use Empp\Model\Presets;
use Empp\Model\Respirator;
use Empp\Model\SimulationInput;
use Empp\Model\SimulationResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ClassificationTest extends TestCase
{
    public function testEn1822Boundaries(): void
    {
        self::assertNull(En1822::classify(0.16));
        self::assertSame('E10', En1822::classify(0.15)['cls']);
        self::assertSame('E12', En1822::classify(0.0029)['cls']);
        self::assertSame('H13', En1822::classify(5e-4)['cls']);
        self::assertSame('U17', En1822::classify(1e-12)['cls']);
    }

    /** Membrane A of the manual: Emin 99.660 % (E12), 413 Pa; H13 needs 16.0 µm, 552 Pa, +21 min */
    public function testNextClassFollowsTheManualExample(): void
    {
        // Manual v2.1, section 7.3: P(MPPS) 0.34 % at 12 µm, 12 µm deposited in 60 min
        $next = En1822::nextClass(0.0034, 413, 12, 60);

        self::assertSame('H13', $next['cls']);
        self::assertEqualsWithDelta(16.0, $next['thickness'], 0.05);
        self::assertEqualsWithDelta(552, $next['dP'], 1);
        self::assertSame(21, $next['extraMinutes']);
    }

    public function testNextClassWithoutDepositionTimeHasNoMinutes(): void
    {
        $next = En1822::nextClass(0.2, 10, 12);

        self::assertSame('E10', $next['cls'], 'below E10 the next class is E10');
        self::assertNull($next['extraMinutes']);
        self::assertNull(En1822::nextClass(1e-12, 10, 12), 'nothing above U17');
    }

    public function testRespiratorLevels(): void
    {
        self::assertSame([], Respirator::levelsMet(0.25));
        self::assertSame(['FFP1', 'FFP2', 'N95'], Respirator::levelsMet(0.05));
        self::assertSame(['FFP1', 'FFP2', 'N95', 'FFP3', 'N99', 'N100'], Respirator::levelsMet(3e-4));
    }

    public function testPresetInputReproducesNioshN(): void
    {
        $r = (new FiltrationModel())->simulate(Presets::input('niosh_n', 'ribeirao_preto', 0.30, 12, 0.841));

        self::assertEqualsWithDelta(99.93988, 100 * $r->efficiency(), 0.5e-5);
    }

    public function testR1AlertWhenMppsIsLargerThanTheFibre(): void
    {
        // Not reachable with realistic inputs (MPPS/df stays below 1), so feed a result directly
        $input = Presets::input('en1822', 'ribeirao_preto', 0.30, 12, 0.841);
        $r = (new FiltrationModel())->simulate($input);
        $r = new SimulationResult(...array_merge(get_object_vars($r), ['mpps' => 0.45]));

        $codes = array_map(static fn ($a) => $a->code, Alerts::evaluate($input, $r));
        self::assertContains('R1', $codes);
    }

    public function testCheckCounter(): void
    {
        $input = SimulationInput::fromArray([
            'T' => 25, 'PmmHg' => 715, 'df' => 0.3, 'L' => 3, 'eps' => 0.841, 'v' => 0.053, 'rhop' => 912,
            'dmin' => 0.06, 'dmax' => 10, 'cmd' => 0.2, 'gsd' => 1.5,
        ]);
        $alerts = Alerts::evaluate($input, (new FiltrationModel())->simulate($input));

        self::assertSame(2, Alerts::countChecks($alerts), 'THIN and EDGE');
        self::assertSame(AlertLevel::NOTE, end($alerts)->level);
        self::assertSame('BASIS', end($alerts)->code, 'BASIS is always last');
    }

    public function testInvalidInputIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Presets::input('en1822', 'ribeirao_preto', 0.30, 12, 1.0);
    }

    public function testV2NeedsTheAerosol(): void
    {
        $input = SimulationInput::fromArray([
            'T' => 25, 'PmmHg' => 715, 'df' => 0.3, 'L' => 12, 'eps' => 0.841, 'v' => 0.053, 'rhop' => 912, 'dmin' => 0.01, 'dmax' => 10,
        ]);

        $this->expectException(InvalidArgumentException::class);
        (new FiltrationModel())->simulate($input);
    }
}
