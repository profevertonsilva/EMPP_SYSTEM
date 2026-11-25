<?php

declare(strict_types=1);

namespace Empp\Service;

use Empp\Model\Presets;
use Empp\Model\SimulationRun;
use Empp\Repository\SimulationRepository;
use PDO;

/**
 * Keeps the default-scenario simulation of a membrane stored and current. Call
 * refresh() whenever the thickness or an image's porosity or fibre diameter
 * changes; membrane cards, Compare and the correlation module read the stored row.
 */
final class StandardSimulation
{
    public const AEROSOL = 'en1822';
    public const AIR = 'ribeirao_preto';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return int|null simulation id; null when the membrane still lacks thickness or
     *                  an analysed image (any stale standard simulation is then removed)
     */
    public function refresh(int $membraneId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT thickness_um FROM membranes WHERE id = ?');
        $stmt->execute([$membraneId]);
        $thickness = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare(
            'SELECT id, porosity, fiber_diameter_um FROM membrane_images
              WHERE membrane_id = ? AND porosity IS NOT NULL AND fiber_diameter_um IS NOT NULL
              ORDER BY sort_order, id'
        );
        $stmt->execute([$membraneId]);
        $images = $stmt->fetchAll();

        $repository = new SimulationRepository($this->pdo);
        if ($thickness === false || $thickness === null || $images === []) {
            $repository->deleteStandard($membraneId);

            return null;
        }
        $L = (float) $thickness;

        // The membrane is simulated with the mean structure; each image is run too, for the spread
        $eps = array_sum(array_column($images, 'porosity')) / count($images);
        $df = array_sum(array_column($images, 'fiber_diameter_um')) / count($images);
        $run = SimulationRun::of(Presets::input(self::AEROSOL, self::AIR, $df, $L, $eps));

        $perImage = [];
        foreach ($images as $image) {
            $one = SimulationRun::of(Presets::input(self::AEROSOL, self::AIR, (float) $image['fiber_diameter_um'], $L, (float) $image['porosity']));
            $perImage[] = [
                'image_id' => (int) $image['id'],
                'porosity' => (float) $image['porosity'],
                'fiber_diameter_um' => (float) $image['fiber_diameter_um'],
                'pen_min' => $one->result->penMin,
                'dp_pa' => $one->result->dP,
                'en1822_class' => $one->en1822['cls'] ?? null,
            ];
        }

        return $repository->saveStandard($membraneId, $run, [
            'aerosol_preset' => self::AEROSOL,
            'air_preset' => self::AIR,
            'per_image' => $perImage,
        ]);
    }
}
