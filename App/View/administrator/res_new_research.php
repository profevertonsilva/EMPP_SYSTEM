<style>
  /* The SEM image from step 1, shown whole at its own proportions */
  .specimen {
    margin: 0;
  }
  .specimen-frame {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    background: var(--empp-stage);
    border-radius: var(--empp-radius);
    overflow: hidden;
  }
  .specimen-frame img {
    display: block;
    width: 100%;
    height: auto;
    max-height: 480px;
    object-fit: contain;
  }
  .specimen figcaption {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.25rem 1rem;
    margin-top: 0.75rem;
    font-size: 0.875rem;
    /* On the page background --empp-muted falls short of 4.5:1 */
    color: var(--empp-text);
  }

  /* Primary action first on phones, full width; side by side from sm up */
  .study-actions {
    display: flex;
    flex-direction: column-reverse;
    gap: 0.5rem;
  }
  .study-actions .btn {
    width: 100%;
  }
  @media (min-width: 576px) {
    .study-actions {
      flex-direction: row;
      justify-content: space-between;
    }
    .study-actions .btn {
      width: auto;
    }
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <?php $empp_step = 2; include __DIR__ . '/../includes/dashboard/new_research_steps.php'; ?>

    <?php if (($_GET['error'] ?? '') === 'number') { ?>
      <div class="alert alert-warning mb-4" role="alert"><strong>The study was not created.</strong> Flow rate, voltage and distance must be positive numbers, using a point (.) as the decimal separator.</div>
    <?php } ?>

    <form name="form_research" action="/dashboard/researcher/research/insert" method="POST" novalidate>
      <div class="row g-4">
        <!-- Uploaded image -->
        <div class="col-lg-5">
          <figure class="specimen">
            <div class="specimen-frame">
              <img src="<?= $_ENV['BASE_IMG'] . 'research/' . rawurlencode($_SESSION['ree_file'] ?? ''); ?>"
                   alt="SEM image uploaded in the previous step">
            </div>
            <figcaption>
              <span>SEM image, as cropped</span>
              <a href="/dashboard/researcher/research/new" class="d-inline-flex align-items-center">
                <i class="bx bx-revision me-1" aria-hidden="true"></i> Use a different image
              </a>
            </figcaption>
          </figure>
        </div>

        <!-- Study data -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-body">
              <fieldset class="empp-fieldset">
                <legend>Study</legend>
                <div class="mb-3">
                  <label class="form-label" for="ree_name">Name</label>
                  <input type="text" class="form-control" name="ree_name" id="ree_name" placeholder="e.g. PCL 12% — sample A" maxlength="100" required>
                </div>
                <div>
                  <label class="form-label" for="ree_description">Description</label>
                  <textarea class="form-control" name="ree_description" id="ree_description" rows="3" placeholder="Polymer, solvent, anything that identifies the sample" required></textarea>
                </div>
              </fieldset>

              <fieldset class="empp-fieldset">
                <legend>
                  Electrospinning parameters
                  <small>Use a point (.) as the decimal separator.</small>
                </legend>
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
              </fieldset>

              <?php // Only lets the form warn when no image was uploaded; the server uses the session value ?>
              <input type="hidden" name="ree_file" id="ree_file" value="<?= htmlspecialchars($_SESSION['ree_file'] ?? ''); ?>">
            </div>
            <div class="card-footer study-actions">
              <a href="/dashboard/researcher/research-studies" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary" id="create-study">
                Create research study <i class="bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
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
    const form = $('form[name="form_research"]');
    const submitBtn = $('#create-study');
    const submitLabel = submitBtn.html();
    const labels = {
        ree_name: 'Name',
        ree_description: 'Description',
        ree_flow: 'Flow rate',
        ree_voltage: 'Applied voltage',
        ree_distance: 'Needle–collector distance'
    };
    const numeric = ['ree_flow', 'ree_voltage', 'ree_distance'];

    function markInvalid(field) {
        field.addClass('is-invalid').attr('aria-invalid', 'true');
    }

    // An error clears as soon as the field is edited, not on the next submit
    form.on('input', '.is-invalid', function() {
        $(this).removeClass('is-invalid').removeAttr('aria-invalid');
    });

    form.on('submit', function(e) {
        e.preventDefault();
        const problems = [];
        let firstInvalid = null;

        Object.keys(labels).forEach(function(id) {
            const field = $('#' + id);
            const value = field.val().trim();
            let problem = null;
            if (value === '') {
                problem = labels[id] + ' is required.';
            } else if (numeric.includes(id) && !(Number(value) > 0)) {
                // Same rule as the server: a positive number with a point as decimal separator
                problem = labels[id] + ' must be a positive number, e.g. 0.03.';
            }
            if (problem) {
                problems.push(problem);
                markInvalid(field);
                firstInvalid = firstInvalid || field;
            }
        });

        if ($('#ree_file').val() === '') {
            problems.unshift('The SEM image is missing. Go back and upload it again.');
        }

        if (problems.length) {
            // returnFocus off: the dialog would otherwise hand focus back to the submit button
            Swal.fire({ title: 'Check the form', html: problems.join('<br>'), icon: 'warning', returnFocus: false })
                .then(function() { if (firstInvalid) firstInvalid.trigger('focus'); });
            return false;
        }

        // One study per click: block a second submit while the first is on its way
        submitBtn.prop('disabled', true).attr('aria-busy', 'true')
            .html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Creating study…');
        this.submit();
    });

    // Coming back with the browser's Back button restores the page from cache:
    // give the button back its normal state
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            submitBtn.prop('disabled', false).removeAttr('aria-busy').html(submitLabel);
        }
    });
});
</script>
