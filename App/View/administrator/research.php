<style>
  #projectsTable .research-thumb {
    width: 96px;
    height: 68px;
    object-fit: cover;
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius-sm);
    background-color: var(--empp-bg);
    display: block;
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <span class="empp-eyebrow">Administration</span>
          <h5 class="mb-0">All Research Studies</h5>
        </div>
        <a href="/dashboard/administrator/dataset" class="btn btn-outline-primary">
          <i class="fa-solid fa-images me-1"></i> Image dataset
        </a>
      </div>
      <div class="table-responsive">
        <table id="projectsTable" class="table table-hover align-middle">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th>Image</th>
              <th>Study</th>
              <th>Researcher</th>
              <th class="text-end">Porosity</th>
              <th>Created at</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php foreach ($this->getView()->researchs as $research) {
              $ree_id = $research->__get('ree_id');
              $ree_name = $research->__get('ree_name');
              $rre_porosity = $research->__get('rre_porosity');
              $has_porosity = $rre_porosity !== null && $rre_porosity !== '';
            ?>
            <tr>
              <td class="text-center text-muted empp-num"><?= (int) $ree_id; ?></td>
              <td>
                <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($research->__get('ree_file')); ?>"
                     alt="<?= htmlspecialchars($ree_name); ?>"
                     class="research-thumb" loading="lazy">
              </td>
              <td class="fw-semibold"><?= htmlspecialchars($ree_name); ?></td>
              <td>
                <a href="/dashboard/administrator/researchers/<?= (int) $research->__get('fk_login_log_id'); ?>"><?= htmlspecialchars($research->__get('res_name')); ?></a>
              </td>
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
  <!-- / Content -->

  <script>
$(document).ready(function() {
  $('#projectsTable').DataTable({
    responsive: true,
    order: [[0, 'desc']],
    columnDefs: [
      { targets: [1, 6], orderable: false, searchable: false }
    ],
    dom: '<"top"lf>rt<"bottom"ip>',
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search studies or researchers...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ studies",
      emptyTable: "No research studies yet.",
      zeroRecords: "No study matches your search.",
      paginate: {
        previous: "Previous",
        next: "Next"
      }
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
