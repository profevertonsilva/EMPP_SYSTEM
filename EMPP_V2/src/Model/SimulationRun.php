<?php

declare(strict_types=1);

namespace Empp\Model;

/** Everything one simulation produces: what the screens show and what gets stored */
final readonly class SimulationRun
{
    /**
     * @param list<Alert>                                                                          $alerts
     * @param array{cls: string, group: string, minEff: float, maxPen: float}|null                 $en1822
     * @param array{ePM1: float|null, ePM25: float|null, ePM10: float|null, group: string|null}    $epm
     */
    public function __construct(
        public SimulationInput $input,
        public SimulationOptions $options,
        public SimulationResult $result,
        public array $alerts,
        public ?array $en1822,
        public array $epm,
        public string $modelVersion = FiltrationModel::VERSION,
    ) {
    }

    public static function of(SimulationInput $input, SimulationOptions $options = new SimulationOptions()): self
    {
        $result = (new FiltrationModel())->simulate($input, $options);

        return new self(
            input: $input,
            options: $options,
            result: $result,
            alerts: Alerts::evaluate($input, $result, $options),
            en1822: En1822::classify($result->penMin),
            epm: Iso16890::evaluate($result->rows),
        );
    }

    public function checkCount(): int
    {
        return Alerts::countChecks($this->alerts);
    }
}
