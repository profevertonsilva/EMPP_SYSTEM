<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <span class="empp-eyebrow">Researchers</span>
            <div class="empp-readout mt-1"><?= (int) $this->getView()->total_researchers; ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <span class="empp-eyebrow">Research studies</span>
            <div class="empp-readout mt-1"><?= (int) $this->getView()->total_research; ?></div>
          </div>
        </div>
      </div>
    </div>

    <?php if (isset($this->getView()->recent)) { ?>
    <!-- Administrator overview: latest activity across all researchers -->
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0">Latest research studies</h5>
        <a href="/dashboard/administrator/research-studies" class="btn btn-sm btn-outline-primary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Study</th>
              <th>Researcher</th>
              <th class="text-end">Porosity</th>
              <th>Created at</th>
              <th></th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php if (!$this->getView()->recent) { ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No research studies yet.</td></tr>
            <?php } ?>
            <?php foreach ($this->getView()->recent as $research) {
              $rre_porosity = $research->__get('rre_porosity');
              $has_porosity = $rre_porosity !== null && $rre_porosity !== '';
            ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($research->__get('ree_name')); ?></td>
              <td>
                <a href="/dashboard/administrator/researchers/<?= (int) $research->__get('fk_login_log_id'); ?>"><?= htmlspecialchars($research->__get('res_name')); ?></a>
              </td>
              <td class="text-end empp-num">
                <?= $has_porosity ? number_format((float) $rre_porosity, 2) . '%' : '<span class="text-muted">&mdash;</span>'; ?>
              </td>
              <td class="text-muted text-nowrap empp-num"><?= htmlspecialchars($research->__get('ree_create')); ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="/dashboard/researcher/research/view/<?= (int) $research->__get('ree_id'); ?>" title="View results">
                  <i class="fa-solid fa-eye"></i>
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
  <!-- / Content -->
