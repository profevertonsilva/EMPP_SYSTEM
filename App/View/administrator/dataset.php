<?php
$stats = $this->getView()->stats;
$by_researcher = $this->getView()->by_researcher;
$missing_images = $stats['studies'] - $stats['images'];
$unprocessed = $stats['studies'] - $stats['features'];
?>
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
      <div>
        <span class="empp-eyebrow">Administration</span>
        <h4 class="mb-1">Image Dataset</h4>
        <p class="text-muted mb-0">SEM images and parameters from every researcher, packaged for retraining the porosity model.</p>
      </div>
      <a class="btn btn-primary" href="/dashboard/administrator/dataset/download">
        <i class="fa-solid fa-download me-1"></i> Download full dataset (.zip)
      </a>
    </div>

    <!-- Totals -->
    <div class="row g-4 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">Research studies</span>
          <div class="empp-readout mt-1"><?= (int) $stats['studies']; ?></div>
        </div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">Images on server</span>
          <div class="empp-readout mt-1"><?= (int) $stats['images']; ?></div>
          <?php if ($missing_images > 0) { ?>
            <small class="text-warning"><?= $missing_images; ?> file<?= $missing_images > 1 ? 's' : ''; ?> missing</small>
          <?php } ?>
        </div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">With Haralick features</span>
          <div class="empp-readout mt-1"><?= (int) $stats['features']; ?></div>
          <?php if ($unprocessed > 0) { ?>
            <small class="text-muted"><?= $unprocessed; ?> not processed</small>
          <?php } ?>
        </div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">With predicted porosity</span>
          <div class="empp-readout mt-1"><?= (int) $stats['predicted']; ?></div>
        </div></div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Per researcher -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">By researcher</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Researcher</th>
                  <th class="text-end">Studies</th>
                  <th class="text-end">Images</th>
                  <th class="text-end">Features</th>
                  <th class="text-center">Export</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php if (!$by_researcher) { ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No research studies yet.</td></tr>
                <?php } ?>
                <?php foreach ($by_researcher as $r) { ?>
                <tr>
                  <td>
                    <a class="fw-semibold d-block" href="/dashboard/administrator/researchers/<?= (int) $r['log_id']; ?>"><?= htmlspecialchars($r['name']); ?></a>
                    <small class="text-muted"><?= htmlspecialchars($r['institution']); ?></small>
                  </td>
                  <td class="text-end empp-num"><?= (int) $r['studies']; ?></td>
                  <td class="text-end empp-num"><?= (int) $r['images']; ?></td>
                  <td class="text-end empp-num"><?= (int) $r['features']; ?></td>
                  <td class="text-center">
                    <a class="btn btn-sm btn-outline-primary" href="/dashboard/administrator/dataset/download?researcher=<?= (int) $r['log_id']; ?>" title="Download this researcher's images and data">
                      <i class="fa-solid fa-download"></i>
                    </a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- What's in the ZIP -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">What's in the ZIP</h5>
          </div>
          <div class="card-body">
            <p class="mb-2"><code>images/</code> &mdash; every SEM image, named <code>&lt;id&gt;_&lt;file&gt;</code>.</p>
            <p class="mb-3"><code>dataset.csv</code> &mdash; one row per study. The first columns follow <code>DatasetV3.csv</code>, so the rows can be appended to it and used by <code>mlops.py</code>.</p>
            <div class="alert alert-warning mb-0" role="alert">
              <strong>Fill in measured porosity before training.</strong>
              The <code>Porosidade</code> column is left empty on purpose. The platform only stores the model's own prediction (exported as a separate column), and training on it would teach the model nothing new.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->
