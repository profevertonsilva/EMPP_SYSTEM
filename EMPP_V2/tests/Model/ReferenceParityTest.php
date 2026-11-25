<?php

declare(strict_types=1);

namespace Empp\Tests\Model;

use Empp\Model\Alert;
use Empp\Model\Alerts;
use Empp\Model\En1822;
use Empp\Model\FiltrationModel;
use Empp\Model\Iso16890;
use Empp\Model\SimulationInput;
use Empp\Model\SimulationOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The PHP port against the JavaScript reference model, number by number.
 * Fixture: tests/fixtures/reference-cases.json (node tests/fixtures/generate-reference.js).
 */
final class ReferenceParityTest extends TestCase
{
    /** Relative tolerance: only floating-point noise between the two languages is accepted */
    private const REL = 1e-9;

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function cases(): iterable
    {
        $json = json_decode((string) file_get_contents(__DIR__ . '/../fixtures/reference-cases.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach ($json['cases'] as $case) {
            $case['rowKeys'] = $json['rowKeys'];
            yield $case['name'] => [$case];
        }
    }

    /** @param array<string, mixed> $case */
    #[DataProvider('cases')]
    public function testScalarsMatchReference(array $case): void
    {
        [, $r] = $this->simulateCase($case);

        foreach ($case['result'] as $key => $expected) {
            $actual = match ($key) {
                'E' => $r->efficiency(),
                default => $r->$key,
            };
            $this->assertClose($expected, $actual, $key);
        }
    }

    /** @param array<string, mixed> $case */
    #[DataProvider('cases')]
    public function testGradeEfficiencyCurveMatchesReference(array $case): void
    {
        [, $r] = $this->simulateCase($case);

        self::assertCount(count($case['rows']), $r->rows);
        foreach ($case['rows'] as $n => $expectedRow) {
            foreach ($case['rowKeys'] as $k => $key) {
                $this->assertClose($expectedRow[$k], $r->rows[$n][$key], "row $n, $key");
            }
        }
    }

    /** @param array<string, mixed> $case */
    #[DataProvider('cases')]
    public function testAlertsClassAndEpmMatchReference(array $case): void
    {
        [$options, $r, $input] = $this->simulateCase($case);

        $expectedAlerts = array_map(
            static fn (array $a): array => ['level' => $a['level'] === 'warn' ? 'check' : 'note', 'code' => $a['code'], 'text' => $a['text']],
            $case['alerts']
        );
        $alerts = array_map(static fn (Alert $a): array => $a->toArray(), Alerts::evaluate($input, $r, $options));
        self::assertSame($expectedAlerts, $alerts);

        self::assertSame($case['en1822'], En1822::classify($r->penMin)['cls'] ?? null);

        $epm = Iso16890::evaluate($r->rows);
        self::assertSame($case['epm']['group'], $epm['group']);
        foreach (['ePM1', 'ePM25', 'ePM10'] as $key) {
            $this->assertClose($case['epm'][$key], $epm[$key], $key);
        }
    }

    /**
     * @param array<string, mixed> $case
     *
     * @return array{SimulationOptions, \Empp\Model\SimulationResult, SimulationInput}
     */
    private function simulateCase(array $case): array
    {
        $input = SimulationInput::fromArray($case['input']);
        $options = SimulationOptions::fromArray($case['options']);

        return [$options, (new FiltrationModel())->simulate($input, $options), $input];
    }

    private function assertClose(mixed $expected, mixed $actual, string $what): void
    {
        if ($expected === null || is_bool($expected)) {
            self::assertSame($expected, $actual, $what);

            return;
        }
        self::assertIsFloat($actual, $what);
        $tolerance = max(abs((float) $expected) * self::REL, 1e-300);
        self::assertEqualsWithDelta((float) $expected, $actual, $tolerance, $what);
    }
}
