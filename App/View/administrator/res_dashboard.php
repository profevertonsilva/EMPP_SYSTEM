<?php
$studies = $this->getView()->researchs ?: [];
$predicted = 0;
foreach ($studies as $s) {
  $p = $s->__get('rre_porosity');
  if ($p !== null && $p !== '') $predicted++;
}
$pending = count($studies) - $predicted;
$recent = array_slice($studies, 0, 6);
$first_name = explode(' ', trim((string) $this->getView()->researcher->res_name))[0];
?>
<style>
  .study-tile {
    display: block;
    color: inherit;
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius);
    background: var(--empp-surface);
    overflow: hidden;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .study-tile:hover {
    color: inherit;
    border-color: var(--empp-primary);
    box-shadow: 0 4px 16px rgba(25, 32, 46, 0.08);
  }
  .study-tile img {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    background: #0e1320;
  }
  .study-tile-body {
    padding: 0.875rem 1rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.75rem;
  }
  .study-tile-body h6 {
    margin: 0;
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .study-tile .empp-readout {
    font-size: 1.125rem;
    white-space: nowrap;
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
      <div>
        <span class="empp-eyebrow">Dashboard</span>
        <h4 class="mb-0">Welcome back, <?= htmlspecialchars($first_name); ?></h4>
      </div>
      <a href="/dashboard/researcher/research/new" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> New research study
      </a>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">Research studies</span>
          <div class="empp-readout mt-1"><?= count($studies); ?></div>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">With predicted porosity</span>
          <div class="empp-readout mt-1"><?= $predicted; ?></div>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
          <span class="empp-eyebrow">Awaiting analysis</span>
          <div class="empp-readout mt-1"><?= $pending; ?></div>
        </div></div>
      </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="mb-0">Latest studies</h5>
      <?php if ($studies) { ?>
        <a href="/dashboard/researcher/research-studies">View all</a>
      <?php } ?>
    </div>

    <?php if (!$studies) { ?>
      <label class="empp-dropzone" onclick="location.href='/dashboard/researcher/research/new'">
        <i class="bx bx-image-add"></i>
        <strong>Start your first research study</strong>
        <span>Upload a SEM image of an electrospun membrane and its process parameters.</span>
      </label>
    <?php } else { ?>
      <div class="row g-4">
        <?php foreach ($recent as $research) {
          $p = $research->__get('rre_porosity');
          $has_p = $p !== null && $p !== ''; ?>
          <div class="col-sm-6 col-lg-4">
            <a class="study-tile" href="/dashboard/researcher/research/view/<?= (int) $research->__get('ree_id'); ?>">
              <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($research->__get('ree_file')); ?>" alt="" loading="lazy">
              <div class="study-tile-body">
                <div style="min-width:0;">
                  <h6><?= htmlspecialchars($research->__get('ree_name')); ?></h6>
                  <small class="text-muted empp-num"><?= htmlspecialchars(substr($research->__get('ree_create'), 0, 10)); ?></small>
                </div>
                <?php if ($has_p) { ?>
                  <div class="text-end">
                    <span class="empp-eyebrow">Porosity</span>
                    <div class="empp-readout"><?= number_format((float) $p, 1); ?><span class="empp-unit">%</span></div>
                  </div>
                <?php } else { ?>
                  <span class="badge bg-label-primary">Not analysed</span>
                <?php } ?>
              </div>
            </a>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
  <!-- / Content -->
