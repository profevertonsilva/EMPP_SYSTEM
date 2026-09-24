<style>
  /* Miniatura da imagem da pesquisa: maior e com cantos retos. */
  #projectsTable .research-thumb {
    width: 110px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--empp-radius-sm);
    border: 1px solid var(--empp-line);
    background-color: #0e1320;
    display: block;
  }

  /* Descricao longa nao pode empurrar a tabela; o texto completo fica no title.
     O clamp fica no span interno: aplicar display:-webkit-box no <td> quebraria
     o display:table-cell e desalinharia a linha inteira. */
  #projectsTable .cell-description {
    max-width: 320px;
    white-space: normal;
  }

  #projectsTable .cell-description span {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  #projectsTable > tbody > tr > td {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper ">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <!-- Contextual Classes -->

      <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <span class="empp-eyebrow">My work</span>
            <h5 class="mb-0">My Research Studies</h5>
          </div>
          <a href="/dashboard/researcher/research/new" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> New research study
          </a>
        </div>
        <div class="table-responsive">
        <table id="projectsTable" class="table table-hover align-middle">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>File</th>
                <th>Name</th>
                <th>Description</th>
                <th class="text-center">Porosity</th>
                <th>Created At</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <?php foreach ($this->getView()->researchs as $research){
                $ree_id = $research->__get('ree_id');
                $ree_name = $research->__get('ree_name');
                $ree_description = $research->__get('ree_description');
                $rre_porosity = $research->__get('rre_porosity');
              ?>
              <tr data-href="/dashboard/researcher/research/view/<?= (int) $ree_id; ?>" tabindex="0" aria-label="Open <?= htmlspecialchars($ree_name); ?>">
                <td class="text-center text-muted empp-num"><?= htmlspecialchars($ree_id); ?></td>
                <td>
                  <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($research->__get('ree_file')); ?>"
                       alt="<?= htmlspecialchars($ree_name); ?>"
                       title="<?= htmlspecialchars($ree_name); ?>"
                       class="research-thumb">
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($ree_name); ?></td>
                <td class="cell-description" title="<?= htmlspecialchars($ree_description); ?>">
                  <span><?= htmlspecialchars($ree_description); ?></span>
                </td>
                <!-- data-order: ordena pelo numero, nao pelo texto "62,50%" / "—". -->
                <td class="text-center" data-order="<?= $rre_porosity !== null && $rre_porosity !== '' ? (float) $rre_porosity : -1 ?>">
                  <?php if ($rre_porosity !== null && $rre_porosity !== '') { ?>
                    <span class="empp-num porosity-value"><?= number_format((float) $rre_porosity, 2) ?>%</span>
                  <?php } else { ?>
                    <span class="text-muted">&mdash;</span>
                  <?php } ?>
                </td>
                <td class="text-muted text-nowrap empp-num"><?= htmlspecialchars($research->__get('ree_create')); ?></td>
                <td class="text-center">
                  <a class="btn btn-sm btn-outline-primary"
                     href="/dashboard/researcher/research/view/<?= htmlspecialchars($ree_id); ?>"
                     title="View Results">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <!--/ Contextual Classes -->
    </div>
  </div>
  <!-- / Content -->

  <script>
$(document).ready(function() {
  // Initialize DataTable
  $('#projectsTable').DataTable({
    responsive: true,
    order: [[0, 'desc']], // Mais recentes primeiro
    // Imagem e acoes nao sao ordenaveis nem pesquisaveis.
    columnDefs: [
      { targets: [1, 6], orderable: false, searchable: false }
    ],
    dom: '<"top"lf>rt<"bottom"ip>',
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search projects...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      emptyTable: "No research studies yet. Click \"New Research\" to create one.",
      zeroRecords: "No research matches your search.",
      paginate: {
        previous: "Previous",
        next: "Next"
      }
    },
    initComplete: function() {
      // Add custom styling to search input
      $('.dataTables_filter input').addClass('form-control');
      $('.dataTables_filter label').contents().filter(function() {
        return this.nodeType === 3;
      }).remove();
    }
  });

  // Reinitialize tooltips (needed because DataTable recreates DOM elements)
  $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>