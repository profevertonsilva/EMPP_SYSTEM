<?php

declare(strict_types=1);

namespace Empp\Tests\Database;

use Empp\Model\Presets;
use Empp\Model\SimulationOptions;
use Empp\Model\SimulationRun;
use Empp\Repository\SimulationRepository;
use Empp\Service\StandardSimulation;

final class SimulationRepositoryTest extends DatabaseTestCase
{
    /** A stored simulation gives back exactly what the model computed, to the last bit */
    public function testStoredRunRoundTripsExactly(): void
    {
        $membrane = $this->membrane();
        $run = SimulationRun::of(Presets::input('niosh_n', 'ribeirao_preto', 0.30, 12, 0.841), new SimulationOptions(inertial: true));
        $repo = new SimulationRepository($this->pdo);

        $row = $repo->find($repo->save($membrane, $run, ['label' => 'NIOSH N, inertial', 'aerosol_preset' => 'niosh_n', 'air_preset' => 'ribeirao_preto']));

        self::assertSame('saved', $row['kind']);
        self::assertNull($this->pdo->query("SELECT standard_simulation_id FROM membranes WHERE id = $membrane")->fetchColumn());
        self::assertSame($run->result->pen, $row['pen']);
        self::assertSame($run->result->penN, $row['pen_number']);
        self::assertSame($run->result->penMin, $row['pen_min']);
        self::assertSame($run->result->dP, $row['dp_pa']);
        self::assertSame($run->result->QF03, $row['qf_03']);
        self::assertSame('E11', $row['en1822_class']);
        self::assertSame(1, $row['opt_inertial']);
        self::assertSame(['SLIP', 'INERT', 'BASIS'], array_column($row['alerts'], 'code'));
        self::assertSame(1, $row['check_count']);
        self::assertSame(array_column($run->result->rows, 'pen'), $row['curve']['pen']);
        self::assertSame(array_column($run->result->rows, 'wn'), $row['curve']['wn']);

        // the stored input reproduces the stored result
        $again = SimulationRun::of(SimulationRepository::inputOf($row), SimulationRepository::optionsOf($row));
        self::assertSame($row['pen'], $again->result->pen);
        self::assertSame($row['dp_pa'], $again->result->dP);
    }

    public function testPenetrationFarBelowDecimalRangeIsKept(): void
    {
        $membrane = $this->membrane();
        $run = SimulationRun::of(Presets::input('en1822', 'ribeirao_preto', 0.05, 12, 0.6));
        $repo = new SimulationRepository($this->pdo);

        $row = $repo->find($repo->save($membrane, $run));

        self::assertLessThan(1e-80, $row['pen_min']);
        self::assertSame($run->result->penMin, $row['pen_min']);
    }

    public function testStandardSimulationUsesMeanStructureAndKeepsItsId(): void
    {
        $membrane = $this->membrane(12.0);
        $first = $this->image($membrane, 0.80, 0.28, 0);
        $this->image($membrane, 0.86, 0.32, 1);
        $service = new StandardSimulation($this->pdo);

        $id = $service->refresh($membrane);
        $row = (new SimulationRepository($this->pdo))->findStandard($membrane);

        self::assertSame($id, (int) $row['id']);
        self::assertSame('standard', $row['kind']);
        self::assertSame($id, $this->pdo->query("SELECT standard_simulation_id FROM membranes WHERE id = $membrane")->fetchColumn());
        self::assertEqualsWithDelta(0.83, $row['porosity'], 1e-12);
        self::assertEqualsWithDelta(0.30, $row['fiber_diameter_um'], 1e-12);
        self::assertSame('en1822', $row['aerosol_preset']);
        self::assertCount(2, $row['per_image']);
        self::assertSame($first, $row['per_image'][0]['image_id']);

        // a new image changes the result in place
        $this->image($membrane, 0.70, 0.30, 2);
        self::assertSame($id, $service->refresh($membrane));
        $updated = (new SimulationRepository($this->pdo))->findStandard($membrane);
        self::assertCount(3, $updated['per_image']);
        self::assertNotSame($row['pen_min'], $updated['pen_min']);
        self::assertSame(1, (int) $this->pdo->query("SELECT COUNT(*) FROM simulations WHERE kind = 'standard'")->fetchColumn());
    }

    public function testStandardSimulationNeedsThicknessAndAnAnalysedImage(): void
    {
        $membrane = $this->membrane(null);
        $this->image($membrane, 0.84, 0.3);
        self::assertNull((new StandardSimulation($this->pdo))->refresh($membrane), 'no thickness');

        $other = $this->membrane(12.0);
        $this->image($other, 0.84, null);
        self::assertNull((new StandardSimulation($this->pdo))->refresh($other), 'no fibre diameter');
    }

    public function testStaleStandardSimulationIsRemoved(): void
    {
        $membrane = $this->membrane(12.0);
        $this->image($membrane, 0.84, 0.3);
        $service = new StandardSimulation($this->pdo);
        self::assertNotNull($service->refresh($membrane));

        $this->pdo->exec("UPDATE membranes SET thickness_um = NULL WHERE id = $membrane");
        self::assertNull($service->refresh($membrane));

        self::assertNull((new SimulationRepository($this->pdo))->findStandard($membrane));
        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM simulations')->fetchColumn());
    }

    /** membranes and simulations point at each other; deleting a membrane must still work */
    public function testDeletingAMembraneWithSimulationsCascades(): void
    {
        $membrane = $this->membrane(12.0);
        $this->image($membrane, 0.84, 0.3);
        (new StandardSimulation($this->pdo))->refresh($membrane);
        (new SimulationRepository($this->pdo))->save($membrane, SimulationRun::of(Presets::input('niosh_n', 'sea_level', 0.3, 12, 0.84)));

        $this->pdo->exec("DELETE FROM membranes WHERE id = $membrane");

        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM simulations')->fetchColumn());
    }
}
