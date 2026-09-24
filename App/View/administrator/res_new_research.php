<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <?php $empp_step = 2; include __DIR__ . '/../includes/dashboard/new_research_steps.php'; ?>

    <?php if (($_GET['error'] ?? '') === 'number') { ?>
      <div class="alert alert-warning mb-4"><strong>The study was not created.</strong> Flow rate, voltage and distance must be positive numbers, using a point (.) as the decimal separator.</div>
    <?php } ?>

    <form name="form_research" action="/dashboard/researcher/research/insert" method="POST" novalidate>
      <div class="row g-4">
        <!-- Uploaded image -->
        <div class="col-lg-5">
          <figure class="mb-0" style="background:#0e1320;border-radius:var(--empp-radius);overflow:hidden;aspect-ratio:4/3;">
            <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($_SESSION['ree_file'] ?? ''); ?>"
                 alt="Uploaded SEM image" style="width:100%;height:100%;object-fit:contain;display:block;">
          </figure>
          <a href="/dashboard/researcher/research/new" class="d-inline-flex align-items-center mt-2">
            <i class="bx bx-revision me-1"></i> Use a different image
          </a>
        </div>

        <!-- Study data -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-body">
              <span class="empp-eyebrow mb-2">Study</span>
              <div class="mb-3">
                <label class="form-label" for="ree_name">Name</label>
                <input type="text" class="form-control" name="ree_name" id="ree_name" placeholder="e.g. PCL 12% — sample A" required>
              </div>
              <div class="mb-4">
                <label class="form-label" for="ree_description">Description</label>
                <textarea class="form-control" name="ree_description" id="ree_description" rows="2" placeholder="Polymer, solvent, anything that identifies the sample" required></textarea>
              </div>

              <span class="empp-eyebrow mb-2">Electrospinning parameters</span>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label" for="ree_flow">Flow rate</label>
                  <div class="input-group">
                    <input type="text" inputmode="decimal" data-decimal class="form-control empp-num" name="ree_flow" id="ree_flow" placeholder="0.03" required>
                    <span class="input-group-text">mL/min</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="ree_voltage">Applied voltage</label>
                  <div class="input-group">
                    <input type="text" inputmode="decimal" data-decimal class="form-control empp-num" name="ree_voltage" id="ree_voltage" placeholder="20" required>
                    <span class="input-group-text">kV</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="ree_distance">Needle–collector distance</label>
                  <div class="input-group">
                    <input type="text" inputmode="decimal" data-decimal class="form-control empp-num" name="ree_distance" id="ree_distance" placeholder="15" required>
                    <span class="input-group-text">cm</span>
                  </div>
                </div>
              </div>

              <input type="hidden" name="ree_file" id="ree_file" value="<?= htmlspecialchars($_SESSION['ree_file'] ?? ''); ?>">
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between gap-2">
              <a href="/dashboard/researcher/research-studies" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                Create research study <i class="bx bx-right-arrow-alt ms-1"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!-- / Content -->

<script>
$(document).ready(function() {
    const labels = {
        ree_name: 'Name',
        ree_description: 'Description',
        ree_flow: 'Flow rate',
        ree_voltage: 'Applied voltage',
        ree_distance: 'Needle–collector distance',
        ree_file: 'SEM image'
    };

    $('form[name="form_research"]').on('submit', function(e) {
        e.preventDefault();
        const problems = [];
        $(this).find('.is-invalid').removeClass('is-invalid');

        Object.keys(labels).forEach(function(id) {
            const field = $('#' + id);
            const value = field.val().trim();
            if (value === '') {
                problems.push(labels[id] + ' is required.');
                field.addClass('is-invalid');
            } else if (['ree_flow', 'ree_voltage', 'ree_distance'].includes(id) && isNaN(value)) {
                problems.push(labels[id] + ' must be a number, with a point (.) as the decimal separator.');
                field.addClass('is-invalid');
            }
        });

        if (problems.length) {
            Swal.fire({ title: 'Check the form', html: problems.join('<br>'), icon: 'warning' });
            return false;
        }
        this.submit();
    });

});
</script>
