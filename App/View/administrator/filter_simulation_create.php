<?php
$ree_id = (int) $this->getView()->ree_id;
$research = $this->getView()->research;
$predicted = $this->getView()->predicted_porosity;
$porosity_fraction = ($predicted !== null && $predicted !== '') ? round((float) $predicted / 100, 4) : '';

// One labelled numeric/text input with its unit
$field = function ($name, $label, $unit = '', $opts = []) {
  $value = $opts['value'] ?? '';
  $type = 'text';
  $mode = ($opts['text'] ?? false) ? '' : ' inputmode="decimal" data-decimal="' . (!empty($opts['signed']) ? 'signed' : '') . '"';
  $cls = ($opts['text'] ?? false) ? 'form-control' : 'form-control empp-num';
  echo '<div class="' . ($opts['col'] ?? 'col-md-6') . '">';
  echo '<label class="form-label" for="' . $name . '">' . $label . '</label>';
  echo $unit ? '<div class="input-group">' : '';
  echo '<input type="' . $type . '"' . $mode . ' class="' . $cls . '" name="' . $name . '" id="' . $name . '"'
     . ' value="' . htmlspecialchars((string) $value) . '"'
     . (isset($opts['placeholder']) ? ' placeholder="' . htmlspecialchars($opts['placeholder']) . '"' : '')
     . ' required>';
  echo $unit ? '<span class="input-group-text">' . $unit . '</span></div>' : '';
  if (!empty($opts['hint'])) echo '<div class="form-text">' . $opts['hint'] . '</div>';
  echo '</div>';
};
?>
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">

    <a href="/dashboard/researcher/research/view/<?= $ree_id; ?>" class="d-inline-flex align-items-center mb-2">
      <i class="bx bx-chevron-left"></i> <?= htmlspecialchars($research->__get('ree_name')); ?>
    </a>
    <span class="empp-eyebrow">Filtration performance simulation</span>
    <h4 class="mb-4">Simulation setup</h4>

    <?php
    $errors = [
      'number'   => 'All numeric fields must contain numbers, using a point (.) as the decimal separator.',
      'porosity' => 'Porosity must be a fraction between 0 and 1 (e.g. 0.80 for 80%).',
      'range'    => 'Particle sizes, thickness, fiber diameter, velocity and pressure must be greater than 0, and the maximum particle size greater than the minimum.',
    ];
    if (isset($_GET['error'], $errors[$_GET['error']])) { ?>
      <div class="alert alert-warning mb-4"><strong>The simulation was not run.</strong> <?= $errors[$_GET['error']]; ?></div>
    <?php } ?>

    <form name="form_research" action="/dashboard/researcher/filterSimulationSave" method="POST">
      <input type="hidden" name="fis_class" id="fis_class" value="100">
      <input type="hidden" name="fk_research_ree_id" id="fk_research_ree_id" value="<?= $ree_id; ?>">

      <div class="row g-4">
        <div class="col-lg-8">
          <!-- Membrane -->
          <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Membrane</h5></div>
            <div class="card-body">
              <div class="row g-3">
                <?php
                $field('fis_material', 'Material', '', ['text' => true, 'col' => 'col-12', 'placeholder' => 'e.g. PAN']);
                $field('fis_porosity', 'Porosity, &epsilon;', '&ndash;', [
                  'value' => $porosity_fraction,
                  'placeholder' => '0.80',
                  'hint' => $porosity_fraction !== ''
                    ? 'Prefilled from this study\'s predicted porosity (' . number_format((float) $predicted, 2) . '%). Fraction between 0 and 1.'
                    : 'Fraction between 0 and 1.',
                ]);
                $field('fis_thickness', 'Thickness, L', 'mm');
                $field('fis_diameter', 'Average fiber diameter, d<sub>f</sub>', '&micro;m');
                $field('fis_density', 'Fiber density, &rho;<sub>f</sub>', 'kg/m&sup3;');
                $field('fis_area', 'Filter area', 'm&sup2;');
                ?>
              </div>
            </div>
          </div>

          <!-- Operating conditions -->
          <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Operating conditions</h5></div>
            <div class="card-body">
              <div class="row g-3">
                <?php
                $field('fis_temperature', 'Temperature, T', '&deg;C', ['col' => 'col-md-4', 'placeholder' => '25', 'signed' => true]);
                $field('fis_pressure', 'Pressure, P', 'mmHg', ['col' => 'col-md-4', 'placeholder' => '760']);
                $field('fis_velocity', 'Air face velocity, v<sub>s</sub>', 'm/s', ['col' => 'col-md-4']);
                ?>
              </div>
            </div>
          </div>

          <!-- Aerosol -->
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Aerosol</h5></div>
            <div class="card-body">
              <div class="row g-3">
                <?php
                $field('fis_size_min', 'Minimum particle size, d<sub>pi,min</sub>', '&micro;m', ['col' => 'col-md-4', 'hint' => 'Smallest size in the distribution.']);
                $field('fis_size_max', 'Maximum particle size, d<sub>pi,max</sub>', '&micro;m', ['col' => 'col-md-4', 'hint' => 'Largest size in the distribution.']);
                $field('fis_concentration', 'Inlet dust concentration, C', 'mg/m&sup3;', ['col' => 'col-md-4']);
                ?>
                <div class="col-md-4">
                  <label class="form-label" for="fis_class1">Size classes</label>
                  <input type="text" class="form-control empp-num" id="fis_class1" value="100" disabled>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary / submit -->
        <div class="col-lg-4">
          <div class="card position-sticky" style="top: 6rem;">
            <div class="card-body">
              <span class="empp-eyebrow mb-2">What you get</span>
              <ul class="ps-3 text-muted mb-4">
                <li class="mb-1">Particle size distribution</li>
                <li class="mb-1">Grade and overall collection efficiency</li>
                <li class="mb-1">Pressure drop and quality factor</li>
              </ul>
              <button type="submit" class="btn btn-primary w-100">
                Run simulation <i class="bx bx-right-arrow-alt ms-1"></i>
              </button>
              <a href="/dashboard/researcher/research/view/<?= $ree_id; ?>" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!-- / Content -->

