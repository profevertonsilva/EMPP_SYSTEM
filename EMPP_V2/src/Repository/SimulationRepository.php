<?php

declare(strict_types=1);

namespace Empp\Repository;

use Empp\Database\Connection;
use Empp\Model\Alert;
use Empp\Model\SimulationInput;
use Empp\Model\SimulationOptions;
use Empp\Model\SimulationRun;
use InvalidArgumentException;
use PDO;

/**
 * Stores simulation runs with their full input, results, alerts and curve, so any
 * screen can show a simulation again without recomputing it.
 */
final class SimulationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{label?: string|null, aerosol_preset?: string|null, air_preset?: string|null,
     *              what_if?: bool, per_image?: list<array<string, mixed>>|null} $meta
     */
    public function save(int $membraneId, SimulationRun $run, array $meta = []): int
    {
        $row = ['membrane_id' => $membraneId, 'kind' => 'saved'] + self::toRow($run, $meta);
        $this->insert($row);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * The default-scenario simulation of a membrane (one per membrane), replaced in
     * place so its id stays the same.
     *
     * @param array{aerosol_preset?: string|null, air_preset?: string|null, per_image?: list<array<string, mixed>>|null} $meta
     */
    public function saveStandard(int $membraneId, SimulationRun $run, array $meta = []): int
    {
        $row = self::toRow($run, ['what_if' => false, 'label' => null] + $meta);

        // Joins the caller's transaction when there is one (the v1 import runs in one)
        $own = !$this->pdo->inTransaction();
        $own && $this->pdo->beginTransaction();
        try {
            $find = $this->pdo->prepare('SELECT standard_simulation_id FROM membranes WHERE id = ? FOR UPDATE');
            $find->execute([$membraneId]);
            $id = $find->fetchColumn();
            if ($id === false) {
                throw new InvalidArgumentException("Membrane $membraneId does not exist");
            }

            if ($id === null) {
                $this->insert(['membrane_id' => $membraneId, 'kind' => 'standard'] + $row);
                $id = (int) $this->pdo->lastInsertId();
                // updated_at kept: recomputing the standard scenario is not an edit of the membrane
                $this->pdo->prepare('UPDATE membranes SET standard_simulation_id = ?, updated_at = updated_at WHERE id = ?')
                    ->execute([$id, $membraneId]);
            } else {
                $sets = implode(', ', array_map(static fn (string $c): string => "`$c` = :$c", array_keys($row)));
                $this->pdo->prepare("UPDATE simulations SET $sets WHERE id = :id")->execute(Connection::params($row + ['id' => $id]));
                $id = (int) $id;
            }
            $own && $this->pdo->commit();
        } catch (\Throwable $e) {
            $own && $this->pdo->rollBack();
            throw $e;
        }

        return $id;
    }

    /** @return array<string, mixed>|null the stored row, JSON columns decoded */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM simulations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : self::decode($row);
    }

    /** @return array<string, mixed>|null */
    public function findStandard(int $membraneId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.* FROM membranes m JOIN simulations s ON s.id = m.standard_simulation_id WHERE m.id = ?');
        $stmt->execute([$membraneId]);
        $row = $stmt->fetch();

        return $row === false ? null : self::decode($row);
    }

    /** Removes the standard simulation of a membrane, if any (the membrane pointer is cleared by the foreign key) */
    public function deleteStandard(int $membraneId): void
    {
        $this->pdo->prepare(
            "DELETE s FROM simulations s JOIN membranes m ON m.standard_simulation_id = s.id WHERE m.id = ?"
        )->execute([$membraneId]);
    }

    /** @return list<array<string, mixed>> saved simulations of a membrane, newest first, without the curve */
    public function listSaved(int $membraneId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, label, aerosol_preset, air_preset, what_if, opt_legacy, opt_inertial, model_version,
                    thickness_um, fiber_diameter_um, porosity, velocity_m_s,
                    pen, pen_number, pen_min, mpps_um, dp_pa, qf, qf_03, en1822_class, check_count, created_at
               FROM simulations WHERE membrane_id = ? AND kind = 'saved' ORDER BY created_at DESC, id DESC"
        );
        $stmt->execute([$membraneId]);

        return $stmt->fetchAll();
    }

    /** Rebuilds the model input a stored simulation was run with */
    public static function inputOf(array $row): SimulationInput
    {
        return new SimulationInput(
            T: (float) $row['temperature_c'],
            PmmHg: (float) $row['pressure_mmhg'],
            df: (float) $row['fiber_diameter_um'],
            L: (float) $row['thickness_um'],
            eps: (float) $row['porosity'],
            v: (float) $row['velocity_m_s'],
            rhop: (float) $row['particle_density_kg_m3'],
            dmin: (float) $row['dmin_um'],
            dmax: (float) $row['dmax_um'],
            classes: (int) $row['size_classes'],
            cmd: $row['cmd_um'] === null ? null : (float) $row['cmd_um'],
            mmd: $row['mmd_um'] === null ? null : (float) $row['mmd_um'],
            gsd: $row['gsd'] === null ? null : (float) $row['gsd'],
            mono: (bool) $row['mono'],
        );
    }

    public static function optionsOf(array $row): SimulationOptions
    {
        return new SimulationOptions(legacy: (bool) $row['opt_legacy'], inertial: (bool) $row['opt_inertial']);
    }

    /**
     * Column values for one run (without membrane_id and kind).
     *
     * @param array<string, mixed> $meta
     *
     * @return array<string, mixed>
     */
    public static function toRow(SimulationRun $run, array $meta = []): array
    {
        $i = $run->input;
        $r = $run->result;
        $unknown = array_diff(array_keys($meta), ['label', 'aerosol_preset', 'air_preset', 'what_if', 'per_image']);
        if ($unknown !== []) {
            throw new InvalidArgumentException('Unknown simulation meta: ' . implode(', ', $unknown));
        }

        return [
            'label' => $meta['label'] ?? null,
            'aerosol_preset' => $meta['aerosol_preset'] ?? null,
            'air_preset' => $meta['air_preset'] ?? null,

            'temperature_c' => $i->T,
            'pressure_mmhg' => $i->PmmHg,
            'velocity_m_s' => $i->v,
            'particle_density_kg_m3' => $i->rhop,
            'cmd_um' => $i->cmd,
            'mmd_um' => $i->mmd,
            'gsd' => $i->gsd,
            'mono' => (int) $i->mono,
            'dmin_um' => $i->dmin,
            'dmax_um' => $i->dmax,
            'size_classes' => $i->classes,
            'thickness_um' => $i->L,
            'fiber_diameter_um' => $i->df,
            'porosity' => $i->eps,
            'what_if' => (int) ($meta['what_if'] ?? false),
            'model_version' => $run->modelVersion,
            'opt_legacy' => (int) $run->options->legacy,
            'opt_inertial' => (int) $run->options->inertial,

            'pen' => $r->pen,
            'pen_number' => $r->penN,
            'pen_min' => $r->penMin,
            'pen_03' => $r->pen03,
            'mpps_um' => $r->mpps,
            'mpps_at_edge' => (int) $r->mppsAtEdge,
            'dp_pa' => $r->dP,
            'viscous_share' => $r->viscShare,
            'qf' => $r->QF,
            'qf_03' => $r->QF03,
            'qf_mpps' => $r->QFmpps,
            'reynolds_fiber' => $r->Ref,
            'knudsen_fiber' => $r->Knf,
            'kuwabara' => $r->Ku,
            'k1_m2' => $r->k1,
            'k1_davies_m2' => $r->k1Davies,
            'slip_factor' => $r->slipFactor,
            'k2_m' => $r->k2,
            'air_viscosity_pa_s' => $r->mu,
            'air_density_kg_m3' => $r->rho,
            'air_mean_free_path_m' => $r->lam,
            'd10_um' => $r->d10,
            'd50_um' => $r->d50,
            'd90_um' => $r->d90,
            'mass_outside' => $r->massOutside,
            'epm1' => $run->epm['ePM1'],
            'epm25' => $run->epm['ePM25'],
            'epm10' => $run->epm['ePM10'],
            'epm_group' => $run->epm['group'],
            'en1822_class' => $run->en1822['cls'] ?? null,
            'check_count' => $run->checkCount(),
            'alerts' => self::json(array_map(static fn (Alert $a): array => $a->toArray(), $run->alerts)),
            'curve' => self::json([
                'd' => array_column($r->rows, 'd'),
                'w' => array_column($r->rows, 'w'),
                'wn' => array_column($r->rows, 'wn'),
                'pen' => array_column($r->rows, 'pen'),
            ]),
            'per_image' => isset($meta['per_image']) ? self::json($meta['per_image']) : null,
        ];
    }

    /** @param array<string, mixed> $row */
    private function insert(array $row): void
    {
        $columns = array_keys($row);
        $sql = sprintf(
            'INSERT INTO simulations (%s) VALUES (%s)',
            implode(', ', array_map(static fn (string $c): string => "`$c`", $columns)),
            implode(', ', array_map(static fn (string $c): string => ":$c", $columns))
        );
        $this->pdo->prepare($sql)->execute(Connection::params($row));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private static function decode(array $row): array
    {
        foreach (['alerts', 'curve', 'per_image'] as $column) {
            if (is_string($row[$column] ?? null)) {
                $row[$column] = json_decode($row[$column], true, flags: JSON_THROW_ON_ERROR);
            }
        }

        return $row;
    }

    private static function json(mixed $value): string
    {
        // serialize_precision -1 (PHP default) writes the shortest exact representation
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}
