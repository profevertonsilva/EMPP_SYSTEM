<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <span class="empp-eyebrow">Administration</span>
          <h5 class="mb-0">Researchers</h5>
        </div>
      </div>
      <div class="table-responsive">
        <table id="researchersTable" class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Researcher</th>
              <th>Institution</th>
              <th>Country</th>
              <th>Role</th>
              <th class="text-end">Studies</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php foreach ($this->getView()->researchers as $researcher) {
              $log_id = $researcher->__get('fk_login_log_id');
              $is_admin = $researcher->__get('log_type') == 'A';
              $photo_url = $researcher->photoUrl();
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar flex-shrink-0">
                    <?php if ($photo_url) { ?>
                      <img src="<?= htmlspecialchars($photo_url); ?>" alt="" class="rounded-circle" />
                    <?php } else { ?>
                      <span class="avatar-initial rounded-circle bg-label-primary"><?= htmlspecialchars($researcher->initial()); ?></span>
                    <?php } ?>
                  </div>
                  <div>
                    <a class="fw-semibold d-block" href="/dashboard/administrator/researchers/<?= (int) $log_id; ?>"><?= htmlspecialchars($researcher->__get('res_name')); ?></a>
                    <small class="text-muted"><?= htmlspecialchars($researcher->__get('log_email')); ?></small>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($researcher->__get('res_institution')); ?></td>
              <td><?= htmlspecialchars($researcher->__get('cou_nicename')); ?></td>
              <td>
                <?php if ($is_admin) { ?>
                  <span class="badge bg-label-primary">Admin</span>
                <?php } else { ?>
                  <span class="text-muted">Researcher</span>
                <?php } ?>
              </td>
              <td class="text-end empp-num"><?= (int) $researcher->__get('research_count'); ?></td>
              <td class="text-center text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="/dashboard/administrator/researchers/<?= (int) $log_id; ?>" title="View studies">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <a class="btn btn-sm btn-outline-primary" href="/dashboard/administrator/researcher/edit/<?= (int) $log_id; ?>" title="Edit profile">
                  <i class="bx bx-edit-alt"></i>
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
  $('#researchersTable').DataTable({
    responsive: true,
    order: [[0, 'asc']],
    columnDefs: [
      { targets: [5], orderable: false, searchable: false }
    ],
    dom: '<"top"lf>rt<"bottom"ip>',
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search researchers...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ researchers",
      emptyTable: "No researchers registered yet.",
      zeroRecords: "No researcher matches your search.",
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
