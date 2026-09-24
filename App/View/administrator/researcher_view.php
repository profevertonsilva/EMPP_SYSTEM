<?php
$profile = $this->getView()->profile;
$log_id = (int) $profile->__get('fk_login_log_id');
$is_admin = $profile->__get('log_type') == 'A';
$photo_url = $profile->photoUrl();
$studies = $this->getView()->researchs;
$processed = 0;
foreach ($studies as $s) {
  $p = $s->__get('rre_porosity');
  if ($p !== null && $p !== '') $processed++;
}
?>
<style>
  #studiesTable .research-thumb {
    width: 96px;
    height: 68px;
    object-fit: cover;
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius-sm);
    background-color: var(--empp-bg);
    display: block;
  }
  .researcher-facts dt {
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--empp-muted);
    margin-top: 1rem;
  }
  .researcher-facts dd {
    margin: 0.125rem 0 0;
    color: var(--empp-ink);
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <a href="/dashboard/administrator/researchers" class="d-inline-flex align-items-center mb-3">
      <i class="bx bx-chevron-left"></i> All researchers
    </a>

    <div class="row g-4">
      <!-- Profile -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="avatar avatar-lg flex-shrink-0">
                <?php if ($photo_url) { ?>
                  <img src="<?= htmlspecialchars($photo_url); ?>" alt="" class="rounded-circle" />
                <?php } else { ?>
                  <span class="avatar-initial rounded-circle bg-label-primary"><?= htmlspecialchars($profile->initial()); ?></span>
                <?php } ?>
              </div>
              <div>
                <h5 class="mb-0"><?= htmlspecialchars($profile->__get('res_name')); ?></h5>
                <a href="mailto:<?= htmlspecialchars($profile->__get('log_email')); ?>"><small><?= htmlspecialchars($profile->__get('log_email')); ?></small></a>
              </div>
            </div>
            <?php if ($is_admin) { ?><span class="badge bg-label-primary">Admin</span><?php } ?>

            <dl class="researcher-facts mb-0">
              <dt>Institution</dt>
              <dd><?= htmlspecialchars($profile->__get('res_institution')); ?></dd>
              <dt>Academic degree</dt>
              <dd><?= htmlspecialchars($profile->__get('res_academic')); ?></dd>
              <dt>Country</dt>
              <dd><?= htmlspecialchars($profile->__get('cou_nicename')); ?></dd>
              <dt>Purpose</dt>
              <dd><?= nl2br(htmlspecialchars($profile->__get('res_purpose'))); ?></dd>
              <?php if ($profile->__get('log_create')) { ?>
              <dt>Member since</dt>
              <dd class="empp-num"><?= htmlspecialchars(substr($profile->__get('log_create'), 0, 10)); ?></dd>
              <?php } ?>
            </dl>
          </div>
          <div class="card-footer d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="/dashboard/administrator/researcher/edit/<?= $log_id; ?>">
              <i class="bx bx-edit-alt me-1"></i> Edit profile
            </a>
          </div>
        </div>
      </div>

      <!-- Studies -->
      <div class="col-lg-8">
        <div class="row g-4 mb-4">
          <div class="col-6">
            <div class="card"><div class="card-body">
              <span class="empp-eyebrow">Research studies</span>
              <div class="empp-readout mt-1"><?= count($studies); ?></div>
            </div></div>
          </div>
          <div class="col-6">
            <div class="card"><div class="card-body">
              <span class="empp-eyebrow">With predicted porosity</span>
              <div class="empp-readout mt-1"><?= $processed; ?></div>
            </div></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Research studies</h5>
            <?php if ($studies) { ?>
            <a class="btn btn-sm btn-primary" href="/dashboard/administrator/dataset/download?researcher=<?= $log_id; ?>">
              <i class="fa-solid fa-download me-1"></i> Download images &amp; data
            </a>
            <?php } ?>
          </div>
          <div class="table-responsive">
            <table id="studiesTable" class="table table-hover align-middle">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th>Image</th>
                  <th>Study</th>
                  <th class="text-end">Porosity</th>
                  <th>Created at</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php foreach ($studies as $research) {
                  $ree_id = $research->__get('ree_id');
                  $ree_name = $research->__get('ree_name');
                  $rre_porosity = $research->__get('rre_porosity');
                  $has_porosity = $rre_porosity !== null && $rre_porosity !== '';
                ?>
                <tr>
                  <td class="text-center text-muted empp-num"><?= (int) $ree_id; ?></td>
                  <td>
                    <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($research->__get('ree_file')); ?>"
                         alt="<?= htmlspecialchars($ree_name); ?>" class="research-thumb" loading="lazy">
                  </td>
                  <td class="fw-semibold"><?= htmlspecialchars($ree_name); ?></td>
                  <td class="text-end empp-num" data-order="<?= $has_porosity ? (float) $rre_porosity : -1 ?>">
                    <?= $has_porosity ? number_format((float) $rre_porosity, 2) . '%' : '<span class="text-muted">&mdash;</span>'; ?>
                  </td>
                  <td class="text-muted text-nowrap empp-num"><?= htmlspecialchars($research->__get('ree_create')); ?></td>
                  <td class="text-center">
                    <a class="btn btn-sm btn-outline-primary" href="/dashboard/researcher/research/view/<?= (int) $ree_id; ?>" title="View results">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

  <script>
$(document).ready(function() {
  $('#studiesTable').DataTable({
    order: [[0, 'desc']],
    columnDefs: [
      { targets: [1, 5], orderable: false, searchable: false }
    ],
    dom: '<"top"f>rt<"bottom"ip>',
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search studies...",
      info: "Showing _START_ to _END_ of _TOTAL_ studies",
      emptyTable: "This researcher has no research studies yet.",
      zeroRecords: "No study matches your search.",
      paginate: { previous: "Previous", next: "Next" }
    },
    initComplete: function() {
      $('.dataTables_filter input').addClass('form-control');
      $('.dataTables_filter label').contents().filter(function() {
        return this.nodeType === 3;
      }).remove();
    }
  });
});
</script>
