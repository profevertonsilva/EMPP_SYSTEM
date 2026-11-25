<?php

declare(strict_types=1);

namespace Empp\Tests\Database;

use Empp\Database\Migrator;
use Empp\Model\Presets;
use PDOException;

final class SchemaTest extends DatabaseTestCase
{
    public function testMigrationsRunOnceAndCreateEveryTable(): void
    {
        $again = (new Migrator($this->pdo, dirname(__DIR__, 2) . '/database/migrations'))->migrate();
        self::assertSame([], $again, 'a second run applies nothing');

        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        sort($tables);
        self::assertSame([
            'aerosol_presets', 'air_presets', 'countries', 'dp_velocity_points', 'fiber_diameters', 'measurements',
            'membrane_images', 'membranes', 'schema_migrations', 'simulations', 'spinning_params', 'users',
        ], $tables);
    }

    /** The seeded presets are the ones the model code uses */
    public function testPresetSeedMatchesModelPresets(): void
    {
        $rows = $this->pdo->query('SELECT * FROM aerosol_presets ORDER BY sort_order')->fetchAll();
        self::assertSame(array_keys(Presets::AEROSOL), array_column($rows, 'code'));
        foreach ($rows as $row) {
            $p = Presets::AEROSOL[$row['code']];
            self::assertSame(
                [$p['name'], $p['standard'], $p['aerosol'], $p['basis'], (float) $p['v'], (float) $p['rhop'], (float) $p['cmd'], (float) $p['gsd'], (int) $p['mono'], (float) $p['dmin'], (float) $p['dmax']],
                [$row['name'], $row['standard'], $row['aerosol'], $row['efficiency_basis'], $row['velocity_cm_s'], $row['particle_density_kg_m3'], $row['cmd_um'], $row['gsd'], $row['mono'], $row['dmin_um'], $row['dmax_um']],
                $row['code']
            );
        }

        $air = $this->pdo->query('SELECT code, name, temperature_c, pressure_mmhg FROM air_presets ORDER BY sort_order')->fetchAll();
        self::assertSame(array_keys(Presets::AIR), array_column($air, 'code'));
        foreach ($air as $row) {
            $a = Presets::AIR[$row['code']];
            self::assertSame([$a['name'], (float) $a['T'], (float) $a['P']], [$row['name'], $row['temperature_c'], $row['pressure_mmhg']]);
        }
    }

    public function testPorosityMustBeAFraction(): void
    {
        $membrane = $this->membrane();

        $this->expectException(PDOException::class);
        $this->image($membrane, 84.1, 0.3); // a percentage, as v1 stored it
    }

    public function testMeasurementNeedsPressureDropOrPenetration(): void
    {
        $membrane = $this->membrane();

        $this->expectException(PDOException::class);
        $this->pdo->prepare('INSERT INTO measurements (membrane_id, velocity_m_s) VALUES (?, 0.053)')->execute([$membrane]);
    }

    public function testDeletingAMembraneRemovesItsData(): void
    {
        $membrane = $this->membrane();
        $image = $this->image($membrane, 0.84, 0.3);
        $this->pdo->prepare('INSERT INTO fiber_diameters (image_id, diameter_um) VALUES (?, 0.31)')->execute([$image]);
        $this->pdo->prepare('INSERT INTO spinning_params (membrane_id, voltage_kv) VALUES (?, 20)')->execute([$membrane]);
        $this->pdo->prepare('INSERT INTO dp_velocity_points (membrane_id, velocity_m_s, dp_pa) VALUES (?, 0.05, 400)')->execute([$membrane]);

        $this->pdo->prepare('DELETE FROM membranes WHERE id = ?')->execute([$membrane]);

        foreach (['membrane_images', 'fiber_diameters', 'spinning_params', 'dp_velocity_points'] as $table) {
            self::assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(), $table);
        }
    }

    public function testStatementSplitterIgnoresCommentsAndBlankParts(): void
    {
        self::assertSame(
            ["CREATE TABLE a (x INT)", "INSERT INTO a VALUES (1),\n(2)"],
            Migrator::statements("-- intro; with semicolon;\nCREATE TABLE a (x INT);\n\n  -- note\nINSERT INTO a VALUES (1),\n(2);\n")
        );
    }
}
