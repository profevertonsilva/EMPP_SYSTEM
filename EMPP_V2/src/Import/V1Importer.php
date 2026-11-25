<?php

declare(strict_types=1);

namespace Empp\Import;

use Empp\Database\Connection;
use Empp\Service\StandardSimulation;
use PDO;

/**
 * Copies the v1 data into the v2 database. Safe to run again (at the cut-over):
 * rows are matched by their v1 id (legacy_* columns) and updated, never duplicated.
 * v1 is the source of truth until the cut-over, so its values win.
 *
 *   v1 country                     -> countries
 *   v1 login + researcher          -> users (password hash kept as is)
 *   v1 research                    -> membranes, spinning_params
 *   v1 research_results (+ file)   -> membrane_images (porosity % -> fraction)
 *   v1 filter_simulation (latest)  -> membrane thickness (mm -> µm), image fibre diameter
 *
 * v1 simulation results are not imported: they came from the v1 equations (with the
 * density bug in the inertial term). Each membrane with thickness, porosity and
 * fibre diameter gets a fresh standard simulation from the v2 model instead.
 *
 * The v1 database is only read, in a read-only session.
 */
final class V1Importer
{
    /** @var array<string, int> */
    private array $stats = [];
    /** @var list<string> */
    private array $warnings = [];

    /**
     * @param string|null $v1Root    v1 application folder, to copy uploaded files from (null: skip files)
     * @param string      $storage   v2 private storage folder (images/ and photos/ inside)
     */
    public function __construct(
        private readonly PDO $v1,
        private readonly PDO $v2,
        private readonly ?string $v1Root,
        private readonly string $storage,
    ) {
        $this->v1->exec('SET SESSION TRANSACTION READ ONLY');
        // v1 TIMESTAMP columns read as UTC, like every v2 DATETIME
        $this->v1->exec("SET time_zone = '+00:00'");
    }

    /** @return array{stats: array<string, int>, warnings: list<string>} */
    public function run(): array
    {
        $this->stats = [];
        $this->warnings = [];

        $this->v2->beginTransaction();
        try {
            $this->countries();
            $users = $this->users();
            $membranes = $this->membranes($users);
            foreach ($membranes as $membraneId) {
                if ((new StandardSimulation($this->v2))->refresh($membraneId) !== null) {
                    $this->count('standard simulations');
                }
            }
            $this->v2->commit();
        } catch (\Throwable $e) {
            $this->v2->rollBack();
            throw $e;
        }

        return ['stats' => $this->stats, 'warnings' => $this->warnings];
    }

    private function countries(): void
    {
        $rows = $this->v1->query('SELECT cou_id, cou_iso, cou_iso3, cou_nicename FROM country ORDER BY cou_id')->fetchAll();
        foreach ($rows as $c) {
            $this->upsert('countries', 'id', (int) $c['cou_id'], [
                'iso2' => strtoupper(trim((string) $c['cou_iso'])),
                'iso3' => self::nullIfBlank($c['cou_iso3']),
                'name' => trim((string) $c['cou_nicename']),
            ], 'countries');
        }
    }

    /** @return array<int, int> v1 log_id => v2 user id */
    private function users(): array
    {
        $rows = $this->v1->query(
            'SELECT l.log_id, l.log_email, l.log_password, l.log_status, l.log_confirmed, l.log_type, l.log_create,
                    r.res_name, r.res_photo, r.res_institution, r.res_purpose, r.res_academic, r.fk_country_cou_id
               FROM login l LEFT JOIN researcher r ON r.fk_login_log_id = l.log_id
              ORDER BY l.log_id, r.res_id'
        )->fetchAll();

        $map = [];
        foreach ($rows as $u) {
            $logId = (int) $u['log_id'];
            if (isset($map[$logId])) {
                $this->warn("v1 login $logId has more than one researcher row; the first one was used");
                continue;
            }
            $email = strtolower(trim((string) $u['log_email']));
            $taken = $this->v2->prepare('SELECT id FROM users WHERE email = ? AND (legacy_login_id IS NULL OR legacy_login_id <> ?)');
            $taken->execute([$email, $logId]);
            if ($taken->fetchColumn() !== false) {
                $this->warn("v1 login $logId: the email is already used by another v2 account; not imported");
                continue;
            }

            $photo = $this->copyFile('researcher', $u['res_photo'], 'photos', "v1 login $logId photo");
            $map[$logId] = $this->upsert('users', 'legacy_login_id', $logId, [
                'email' => $email,
                'password_hash' => (string) $u['log_password'],
                'role' => $u['log_type'] === 'A' ? 'admin' : 'researcher',
                'status' => $u['log_status'] === 'I' ? 'inactive' : 'active',
                'email_verified_at' => $u['log_confirmed'] === 'Y' ? $u['log_create'] : null,
                'name' => self::nullIfBlank($u['res_name']),
                'institution' => self::nullIfBlank($u['res_institution']),
                'academic_level' => self::nullIfBlank($u['res_academic']),
                'purpose' => self::nullIfBlank($u['res_purpose']),
                'country_id' => $u['fk_country_cou_id'] === null ? null : (int) $u['fk_country_cou_id'],
                'photo' => $photo,
                'created_at' => $u['log_create'],
            ], 'users');
        }

        return $map;
    }

    /**
     * @param array<int, int> $users
     *
     * @return list<int> v2 membrane ids
     */
    private function membranes(array $users): array
    {
        $rows = $this->v1->query(
            'SELECT r.ree_id, r.ree_name, r.ree_description, r.ree_file, r.ree_flow, r.ree_voltage, r.ree_distance,
                    r.ree_create, r.fk_login_log_id
               FROM research r ORDER BY r.ree_id'
        )->fetchAll();
        $results = $this->latestBy('SELECT * FROM research_results ORDER BY rre_id', 'fk_research_ree_id');
        $filters = $this->latestBy('SELECT * FROM filter_simulation ORDER BY fis_id', 'fk_research_ree_id');

        $ids = [];
        foreach ($rows as $r) {
            $reeId = (int) $r['ree_id'];
            $userId = $users[(int) $r['fk_login_log_id']] ?? null;
            if ($userId === null) {
                $this->warn("v1 research $reeId: its owner was not imported; skipped");
                continue;
            }
            $fis = $filters[$reeId] ?? null;
            $rre = $results[$reeId] ?? null;

            // v1 filter simulation form: thickness in mm, fibre diameter in µm
            $thicknessMm = self::positive($fis['fis_thickness'] ?? null);
            $membraneId = $this->upsert('membranes', 'legacy_research_id', $reeId, [
                'user_id' => $userId,
                'name' => trim((string) $r['ree_name']) ?: "Research $reeId",
                'description' => self::nullIfBlank($r['ree_description']),
                'thickness_um' => $thicknessMm === null ? null : $thicknessMm * 1000,
                'thickness_source' => $thicknessMm === null ? null : 'measured',
                'created_at' => $r['ree_create'],
            ], 'membranes');
            $ids[] = $membraneId;
            if ($thicknessMm === null) {
                $this->warn("v1 research $reeId: no thickness in v1; the membrane needs it before it can be simulated");
            }

            $this->upsert('spinning_params', 'membrane_id', $membraneId, [
                'voltage_kv' => self::positive($r['ree_voltage']),
                'flow_rate_ml_min' => self::positive($r['ree_flow']),
                'distance_cm' => self::positive($r['ree_distance']),
            ], 'spinning params');

            $porosityPct = self::positive($rre['rre_porosity'] ?? null);
            if ($porosityPct !== null && $porosityPct >= 100) {
                $this->warn("v1 research $reeId: porosity $porosityPct % is not valid; left empty");
                $porosityPct = null;
            }
            $diameter = self::positive($fis['fis_diameter'] ?? null);
            $file = $this->copyFile('research', $r['ree_file'], 'images', "v1 research $reeId image");

            $this->upsert('membrane_images', 'legacy_research_id', $reeId, [
                'membrane_id' => $membraneId,
                'file_name' => $file ?? basename((string) $r['ree_file']),
                'porosity' => $porosityPct === null ? null : $porosityPct / 100,
                'porosity_method' => $porosityPct === null ? null : 'spinning_regression',
                'fiber_diameter_um' => $diameter,
                'fiber_diameter_method' => $diameter === null ? null : 'manual',
                'haralick_dissimilarity' => self::number($rre['rre_dissimilarity'] ?? null),
                'haralick_correlation' => self::number($rre['rre_correlation'] ?? null),
                'haralick_energy' => self::number($rre['rre_energy'] ?? null),
                'haralick_homogeneity' => self::number($rre['rre_homogeneity'] ?? null),
                'analysis_status' => $rre === null ? 'pending' : 'done',
                'created_at' => $r['ree_create'],
            ], 'images');
        }

        return $ids;
    }

    /**
     * Inserts or updates the row whose $key equals $value.
     *
     * @param array<string, mixed> $values
     *
     * @return int the row id ($value itself when $key is the primary key)
     */
    private function upsert(string $table, string $key, int $value, array $values, string $label): int
    {
        $idColumn = ['spinning_params' => 'membrane_id'][$table] ?? 'id';
        $find = $this->v2->prepare("SELECT `$idColumn` FROM `$table` WHERE `$key` = ?");
        $find->execute([$value]);
        $id = $find->fetchColumn();

        if ($id === false) {
            $row = [$key => $value] + $values;
            $cols = array_keys($row);
            $this->v2->prepare(sprintf(
                'INSERT INTO `%s` (%s) VALUES (%s)',
                $table,
                implode(', ', array_map(static fn ($c) => "`$c`", $cols)),
                implode(', ', array_map(static fn ($c) => ":$c", $cols))
            ))->execute(Connection::params($row));
            $this->count("$label inserted");

            return $key === $idColumn ? $value : (int) $this->v2->lastInsertId();
        }

        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($values)));
        $this->v2->prepare("UPDATE `$table` SET $sets WHERE `$key` = :__key")->execute(Connection::params($values + ['__key' => $value]));
        $this->count("$label updated");

        return (int) $id;
    }

    /**
     * Copies an uploaded v1 file into v2 private storage (once).
     *
     * @return string|null stored file name, null when there is no file or it cannot be found
     */
    private function copyFile(string $v1Folder, mixed $name, string $v2Folder, string $what): ?string
    {
        $name = basename(trim((string) $name));
        if ($name === '' || $name === '.' || !preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            return null;
        }
        $target = $this->storage . "/$v2Folder/$name";
        if (is_file($target)) {
            return $name;
        }
        if ($this->v1Root === null) {
            return $name;
        }
        $source = $this->v1Root . "/resources/dashboard/assets/img/$v1Folder/$name";
        if (!is_file($source)) {
            $this->warn("$what: file $name not found in v1");

            return null;
        }
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        copy($source, $target);
        $this->count("$v2Folder copied");

        return $name;
    }

    /** @return array<int, array<string, mixed>> last row per value of $column */
    private function latestBy(string $sql, string $column): array
    {
        $by = [];
        foreach ($this->v1->query($sql)->fetchAll() as $row) {
            $by[(int) $row[$column]] = $row;
        }

        return $by;
    }

    private static function number(mixed $v): ?float
    {
        return is_numeric($v) ? (float) $v : null;
    }

    private static function positive(mixed $v): ?float
    {
        $n = self::number($v);

        return $n !== null && $n > 0 && is_finite($n) ? $n : null;
    }

    private static function nullIfBlank(mixed $v): ?string
    {
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    private function count(string $what): void
    {
        $this->stats[$what] = ($this->stats[$what] ?? 0) + 1;
    }

    private function warn(string $message): void
    {
        $this->warnings[] = $message;
    }
}
