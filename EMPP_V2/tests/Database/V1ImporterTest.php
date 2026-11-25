<?php

declare(strict_types=1);

namespace Empp\Tests\Database;

use Empp\Database\Migrator;
use Empp\Import\V1Importer;
use Empp\Repository\SimulationRepository;
use PDO;

final class V1ImporterTest extends DatabaseTestCase
{
    private PDO $v1;
    private string $v1Root;
    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->v1 = self::freshDatabase('TEST_V1_DB_NAME');
        foreach (['schema', 'data'] as $file) {
            foreach (Migrator::statements((string) file_get_contents(dirname(__DIR__) . "/fixtures/v1/$file.sql")) as $sql) {
                $this->v1->exec($sql);
            }
        }

        $base = sys_get_temp_dir() . '/empp-import-' . bin2hex(random_bytes(4));
        $this->v1Root = "$base/v1";
        $this->storage = "$base/storage";
        foreach (['research' => ['aaa111.jpg', 'ccc333.jpg'], 'researcher' => ['ana.png']] as $folder => $files) {
            mkdir("$this->v1Root/resources/dashboard/assets/img/$folder", 0777, true);
            foreach ($files as $name) {
                file_put_contents("$this->v1Root/resources/dashboard/assets/img/$folder/$name", "image $name");
            }
        }
    }

    protected function tearDown(): void
    {
        $base = dirname($this->v1Root);
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($base);
    }

    /** Runs the importer on its own (read-only) v1 connection; $this->v1 stays writable for the tests */
    private function import(): array
    {
        $v1 = \Empp\Database\Connection::open(self::settings('TEST_V1_DB_NAME'));

        return (new V1Importer($v1, $this->pdo, $this->v1Root, $this->storage))->run();
    }

    public function testImportsUsersWithTheirPasswordsAndRoles(): void
    {
        $this->import();
        $users = $this->pdo->query('SELECT * FROM users ORDER BY legacy_login_id')->fetchAll();

        self::assertSame([1, 2, 3, 4], array_column($users, 'legacy_login_id'));
        [$ana, $bruno, $admin, $old] = $users;

        self::assertSame('ana@example.test', $ana['email'], 'trimmed and lower-cased');
        self::assertTrue(password_verify('secret', $ana['password_hash']));
        self::assertSame(sha1('secret'), $bruno['password_hash'], 'legacy SHA-1 kept for the rehash on first sign-in');
        self::assertSame('Ana Lima', $ana['name']);
        self::assertSame(30, $ana['country_id']);
        self::assertSame('ana.png', $ana['photo']);
        self::assertFileExists("$this->storage/photos/ana.png");
        self::assertSame('2025-03-10 12:00:00', $ana['email_verified_at']);
        self::assertNull($bruno['email_verified_at'], 'unconfirmed in v1');
        self::assertNull($bruno['photo'], 'blank photo');
        self::assertSame('admin', $admin['role']);
        self::assertNull($admin['name'], 'administrator without researcher row');
        self::assertSame('inactive', $old['status']);
    }

    public function testImportsResearchAsMembranesWithImages(): void
    {
        $report = $this->import();

        $m = $this->pdo->query('SELECT * FROM membranes ORDER BY legacy_research_id')->fetchAll();
        self::assertSame([1, 2, 3], array_column($m, 'legacy_research_id'), 'research 4 has no owner');
        self::assertEqualsWithDelta(12.0, $m[0]['thickness_um'], 1e-9, '0.012 mm from the latest filter simulation');
        self::assertNull($m[1]['thickness_um']);
        self::assertNull($m[1]['description'], 'blank description');

        $img = $this->pdo->query('SELECT * FROM membrane_images ORDER BY legacy_research_id')->fetchAll();
        self::assertEqualsWithDelta(0.841, $img[0]['porosity'], 1e-12, 'v1 percent -> fraction');
        self::assertSame('spinning_regression', $img[0]['porosity_method']);
        self::assertEqualsWithDelta(0.30, $img[0]['fiber_diameter_um'], 1e-12);
        self::assertSame('manual', $img[0]['fiber_diameter_method']);
        self::assertSame(36.94458019574228, $img[0]['haralick_dissimilarity']);
        self::assertSame('done', $img[0]['analysis_status']);
        self::assertFileExists("$this->storage/images/aaa111.jpg");
        self::assertSame('pending', $img[1]['analysis_status']);
        self::assertNull($img[2]['porosity'], '150 % rejected');

        $spin = $this->pdo->query('SELECT * FROM spinning_params ORDER BY membrane_id')->fetchAll();
        self::assertSame([20.0, 0.03, 15.0], [$spin[0]['voltage_kv'], $spin[0]['flow_rate_ml_min'], $spin[0]['distance_cm']]);
        self::assertNull($spin[1]['flow_rate_ml_min'], 'zero flow is no value');

        $warnings = implode("\n", $report['warnings']);
        self::assertStringContainsString('research 4: its owner was not imported', $warnings);
        self::assertStringContainsString('research 2: no thickness', $warnings);
        self::assertStringContainsString('missing.jpg not found', $warnings);
        self::assertStringContainsString('porosity 150 %', $warnings);
    }

    /** Membrane 1 is the v2 reference structure: its standard simulation is the published EN 1822 case */
    public function testCreatesStandardSimulationWithTheV2Model(): void
    {
        $report = $this->import();
        self::assertSame(1, $report['stats']['standard simulations']);

        $id = (int) $this->pdo->query('SELECT id FROM membranes WHERE legacy_research_id = 1')->fetchColumn();
        $sim = (new SimulationRepository($this->pdo))->findStandard($id);

        self::assertSame('E12', $sim['en1822_class']);
        self::assertEqualsWithDelta(99.7103, 100 * (1 - $sim['pen_min']), 0.5e-4);
        self::assertEqualsWithDelta(424.91, $sim['dp_pa'], 0.005);
        self::assertSame('2.1', $sim['model_version']);
    }

    public function testRunningAgainUpdatesInsteadOfDuplicating(): void
    {
        $this->import();
        $this->v1->exec("UPDATE research SET ree_name = 'PAN 10 % (renamed)' WHERE ree_id = 1");
        $this->v1->exec("INSERT INTO research (ree_id, ree_name, ree_description, ree_file, ree_flow, ree_voltage, ree_distance, ree_create, fk_login_log_id)
                         VALUES (5, 'New one', 'x', 'ccc333.jpg', 1, 20, 15, '2025-06-01 00:00:00', 2)");
        $simId = $this->pdo->query('SELECT standard_simulation_id FROM membranes WHERE legacy_research_id = 1')->fetchColumn();

        $report = $this->import();

        $counts = fn (string $t): int => (int) $this->pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        self::assertSame(4, $counts('users'));
        self::assertSame(4, $counts('membranes'));
        self::assertSame(4, $counts('membrane_images'));
        self::assertSame(2, $counts('countries'));
        self::assertSame(1, $counts('simulations'));
        self::assertSame('PAN 10 % (renamed)', $this->pdo->query('SELECT name FROM membranes WHERE legacy_research_id = 1')->fetchColumn());
        self::assertSame($simId, $this->pdo->query('SELECT standard_simulation_id FROM membranes WHERE legacy_research_id = 1')->fetchColumn());
        self::assertSame(1, $report['stats']['membranes inserted']);
        self::assertSame(3, $report['stats']['membranes updated']);
    }

    public function testV1IsOnlyRead(): void
    {
        $before = $this->v1->query('CHECKSUM TABLE country, login, researcher, research, research_results, filter_simulation')->fetchAll();
        $this->import();
        $after = $this->v1->query('CHECKSUM TABLE country, login, researcher, research, research_results, filter_simulation')->fetchAll();

        self::assertSame($before, $after);
    }

    public function testFailedImportLeavesNothingBehind(): void
    {
        // a v1 name longer than the v2 column aborts the import half-way (after users, countries...)
        $this->v1->exec('ALTER TABLE research MODIFY ree_name varchar(300) NOT NULL');
        $this->v1->exec("UPDATE research SET ree_name = REPEAT('n', 200) WHERE ree_id = 3");

        try {
            $this->import();
            self::fail('the import should have failed');
        } catch (\PDOException) {
        }

        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM membranes')->fetchColumn());
    }
}
