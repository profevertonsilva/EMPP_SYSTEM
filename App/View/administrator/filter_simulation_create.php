<?php
$ree_id = (int) $this->getView()->ree_id;
$research = $this->getView()->research;
$predicted = $this->getView()->predicted_porosity;
$has_prediction = $predicted !== null && $predicted !== '';
$porosity_fraction = $has_prediction ? round((float) $predicted / 100, 4) : '';
// Material starts from the study's description (one line, within the 100-character column)
$material = trim(preg_replace('/\s+/', ' ', (string) $research->__get('ree_description')));
$material = mb_substr($material, 0, 100);
$image_url = $_ENV['BASE_IMG'] . 'research/' . rawurlencode(basename((string) $research->__get('ree_file')));

// One labelled numeric/text input with its unit
$field = function ($name, $label, $unit = '', $opts = []) {
  $value = $opts['value'] ?? '';
  $type = 'text';
  $mode = ($opts['text'] ?? false) ? '' : ' inputmode="decimal" data-decimal="' . (!empty($opts['signed']) ? 'signed' : '') . '"';
  $cls = ($opts['text'] ?? false) ? 'form-control' : 'form-control empp-num';
  echo '<div class="' . ($opts['col'] ?? 'col-md-6') . '">';
  echo '<label class="form-label" for="' . $name . '">' . $label . '</label>';
  echo $unit ? '<div class="input-group">' : '';
  $locked = !empty($opts['locked']);
  // A locked field is shown disabled; disabled inputs are not submitted, so a
  // hidden twin carries the value (the server does not trust it either)
  echo '<input type="' . $type . '"' . ($locked ? '' : $mode) . ' class="' . $cls . '"'
     . ($locked ? ' disabled' : ' name="' . $name . '"') . ' id="' . $name . '"'
     . ' value="' . htmlspecialchars((string) $value) . '"'
     . (isset($opts['placeholder']) ? ' placeholder="' . htmlspecialchars($opts['placeholder']) . '"' : '')
     . ($locked ? ' aria-describedby="' . $name . '-hint"' : ' required') . '>';
  if ($locked) echo '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars((string) $value) . '">';
  echo $unit ? '<span class="input-group-text">' . $unit . '</span></div>' : '';
  if (!empty($opts['hint'])) echo '<div class="form-text" id="' . $name . '-hint">' . $opts['hint'] . '</div>';
  echo '</div>';
};

// Setup sections: numbered like every other step path in EMPP
$sections = [
  1 => ['id' => 'sec-filter', 'title' => 'Filter features', 'note' => 'The filter medium: what it is made of and how it is built.'],
  2 => ['id' => 'sec-operation', 'title' => 'Filter operation', 'note' => 'The conditions of the filtration test.'],
  3 => ['id' => 'sec-aerosol', 'title' => 'Aerosol properties', 'note' => 'The particles challenging the filter.'],
];
?>
<style>
  /* Setup sheet: one white sheet, the source study on top, numbered sections below */
  .sim-sheet {
    overflow: hidden;
  }

  /* Source study: the image and the prediction this simulation starts from */
  .sim-source {
    display: grid;
    grid-template-columns: minmax(0, 13rem) minmax(0, 1fr) auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--empp-line);
    background: var(--empp-bg);
  }
  .sim-stage {
    position: relative;
    margin: 0;
    aspect-ratio: 4 / 3;
    border-radius: var(--empp-radius-sm);
    background: var(--empp-stage);
    overflow: hidden;
  }
  .sim-stage img {
    display: block;
    width: 100%;
    height: calc(100% - 1.5rem);
    object-fit: cover;
  }
  .sim-stage figcaption {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 1.5rem;
    padding: 0 0.625rem;
    display: flex;
    align-items: center;
    background: var(--empp-stage);
    color: #c9cfdf;
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    letter-spacing: 0.04em;
  }
  .sim-source-text h5 {
    margin: 0 0 0.25rem;
    font-size: 1.25rem;
  }
  .sim-source-text p {
    margin: 0;
    color: var(--empp-muted-ground, #5b6376);
    font-size: 0.8125rem;
  }
  .sim-source-carry {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem 1rem;
    margin-top: 0.75rem !important;
  }
  .sim-source-carry span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
  }
  .sim-porosity {
    min-width: 10rem;
    padding-left: 1.5rem;
    border-left: 1px solid var(--empp-line-strong);
  }
  .sim-porosity dt {
    color: var(--empp-text);
    font-size: 0.8125rem;
    font-weight: 400;
  }
  .sim-porosity dd {
    margin: 0.25rem 0 0;
  }
  .sim-porosity .empp-readout {
    font-size: 2.25rem;
  }
  .sim-porosity small {
    display: block;
    margin-top: 0.375rem;
    color: var(--empp-muted-ground, #5b6376);
    font-size: 0.8125rem;
  }

  /* Numbered sections */
  .sim-section {
    padding: 1.75rem 1.5rem;
    border: 0;
    margin: 0;
    min-width: 0;
  }
  .sim-section + .sim-section {
    border-top: 1px solid var(--empp-line);
  }
  .sim-section > legend {
    float: left;
    width: 100%;
    display: grid;
    grid-template-columns: 2rem 1fr;
    column-gap: 0.875rem;
    margin: 0 0 0.5rem;
    padding: 0;
  }
  .sim-section > legend + * {
    clear: left;
  }
  /* The gutter row already opens with its own top gap */
  .sim-section > legend + .row {
    margin-top: 0;
  }
  /* Field hints: the template's pale grey falls short of 4.5:1 on white */
  .sim-sheet .form-text {
    color: var(--empp-muted);
  }
  .sim-section legend strong {
    grid-column: 2;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--empp-ink);
    line-height: 1.3;
  }
  .sim-section legend small {
    grid-column: 2;
    color: var(--empp-muted);
    font-size: 0.8125rem;
    font-weight: 400;
  }
  .sim-section .row {
    padding-left: 2.875rem;
  }

  /* Step marker: pending ring, done fill (DESIGN.md step markers) */
  .sim-marker {
    grid-row: 1 / span 2;
    display: grid;
    place-items: center;
    width: 2rem;
    height: 2rem;
    border: 2px solid var(--empp-line-strong);
    border-radius: 50%;
    background: var(--empp-surface);
    color: var(--empp-muted);
    font-size: 0.8125rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
  }
  .is-started .sim-marker {
    border-color: var(--empp-primary);
    color: var(--empp-primary);
  }
  .is-complete .sim-marker {
    border-color: var(--empp-primary);
    background: var(--empp-primary);
    color: #fff;
  }
  .sim-marker .bx {
    font-size: 1rem;
  }

  .form-label .bx-lock-alt {
    margin-left: 0.25rem;
    color: var(--empp-muted);
    vertical-align: -1px;
  }

  /* Run panel: completeness of the setup, then the action */
  /* Setup + converter follow the scroll only when both fit the window */
  @media (min-width: 992px) and (min-height: 980px) {
    .sim-aside {
      position: sticky;
      top: 6rem;
    }
  }
  .sim-run h5,
  .sim-convert h5 {
    margin: 0 0 0.25rem;
    font-size: 0.9375rem;
  }
  .sim-run > .card-body > p,
  .sim-convert > .card-body > p {
    margin: 0 0 1.25rem;
    color: var(--empp-muted);
    font-size: 0.8125rem;
  }
  .sim-check {
    margin: 0 0 1.5rem;
    padding: 0;
    list-style: none;
    border-top: 1px solid var(--empp-line);
  }
  .sim-check li {
    display: grid;
    grid-template-columns: 2rem 1fr auto;
    align-items: center;
    column-gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--empp-line);
    font-size: 0.9375rem;
  }
  .sim-check a {
    color: var(--empp-ink);
    text-decoration: none;
    font-weight: 500;
  }
  .sim-check a:hover {
    color: var(--empp-primary);
    text-decoration: underline;
  }
  .sim-check .sim-marker {
    grid-row: auto;
  }
  .sim-count {
    font-family: var(--empp-mono);
    font-variant-numeric: tabular-nums;
    font-size: 0.8125rem;
    color: var(--empp-muted);
  }
  .is-complete .sim-count {
    color: var(--empp-primary);
  }
  .sim-outputs {
    margin: 0 0 1.5rem;
    padding: 0;
    list-style: none;
    color: var(--empp-text);
    font-size: 0.8125rem;
  }
  .sim-outputs li {
    display: flex;
    gap: 0.5rem;
    padding: 0.2rem 0;
  }
  .sim-outputs .bx {
    color: var(--empp-primary);
    font-size: 1rem;
  }
  /* Unit converter */
  .sim-convert .conv-unit {
    flex: 0 0 auto;
    width: auto;
    max-width: 45%;
    background-color: var(--empp-bg);
  }
  .conv-swap {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0;
    border: 0;
    background: none;
    color: var(--empp-primary);
    font-size: 0.75rem;
    font-weight: 500;
  }
  .conv-swap:hover {
    color: var(--empp-primary-hover);
  }
  .conv-result {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem 0.875rem;
    background: var(--empp-bg);
    border-radius: var(--empp-radius-sm);
  }
  .conv-result-value {
    min-width: 0;
    overflow-wrap: anywhere;
    line-height: 1.2;
  }
  #conv-out {
    font-size: 1.375rem;
    font-weight: 500;
    color: var(--empp-ink);
  }
  .conv-result-unit {
    margin-left: 0.3em;
    font-family: var(--empp-mono);
    font-size: 0.8125rem;
    color: var(--empp-muted);
  }
  .conv-copy {
    flex: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: var(--empp-surface);
  }
  .conv-copy.is-copied {
    color: var(--empp-success);
    border-color: var(--empp-success);
  }

  .sim-outputs-title {
    margin: 0 0 0.5rem;
    color: var(--empp-ink);
    font-size: 0.9375rem;
    font-weight: 600;
  }

  @media (max-width: 767.98px) {
    .sim-source {
      grid-template-columns: minmax(0, 8rem) minmax(0, 1fr);
      padding: 1.25rem;
    }
    .sim-porosity {
      grid-column: 1 / -1;
      padding: 1rem 0 0;
      border-left: 0;
      border-top: 1px solid var(--empp-line-strong);
    }
    .sim-section {
      padding: 1.5rem 1.25rem;
    }
    .sim-section .row {
      padding-left: 0;
    }
  }
  @media (prefers-reduced-motion: reduce) {
    .sim-marker { transition: none; }
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">

    <a href="/dashboard/researcher/research/view/<?= $ree_id; ?>" class="d-inline-flex align-items-center mb-2">
      <i class="bx bx-chevron-left"></i> <?= htmlspecialchars($research->__get('ree_name')); ?>
    </a>
    <h4 class="mb-1">Filtration simulation</h4>
    <p class="mb-4" style="color: var(--empp-muted-ground, #5b6376);">Describe the membrane, the air and the aerosol. EMPP computes how this membrane filters.</p>

    <?php
    $errors = [
      'number'   => 'All numeric fields must contain numbers, using a point (.) as the decimal separator.',
      'porosity' => 'Porosity must be a fraction between 0 and 1 (e.g. 0.80 for 80%).',
      'range'    => 'Particle sizes, thickness, fiber diameter, velocity and pressure must be greater than 0, and the maximum particle size greater than the minimum.',
    ];
    if (isset($_GET['error'], $errors[$_GET['error']])) { ?>
      <div class="alert alert-warning mb-4" role="alert"><strong>The simulation was not run.</strong> <?= $errors[$_GET['error']]; ?></div>
    <?php } ?>

    <form name="form_research" action="/dashboard/researcher/filterSimulationSave" method="POST" id="sim-form">
      <input type="hidden" name="fis_class" id="fis_class" value="100">
      <input type="hidden" name="fk_research_ree_id" id="fk_research_ree_id" value="<?= $ree_id; ?>">

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card sim-sheet">

            <!-- Where the simulation starts: this study's image and prediction -->
            <section class="sim-source" aria-labelledby="sim-source-title">
              <figure class="sim-stage">
                <img src="<?= $image_url; ?>" alt="SEM image of <?= htmlspecialchars($research->__get('ree_name')); ?>">
                <figcaption>SEM image</figcaption>
              </figure>
              <div class="sim-source-text">
                <h5 id="sim-source-title"><?= htmlspecialchars($research->__get('ree_name')); ?></h5>
                <?php if ($material !== '') { ?>
                  <p><?= htmlspecialchars($material); ?></p>
                <?php } ?>
                <p class="sim-source-carry">
                  <?php if ($material !== '') { ?><span><i class="bx bx-subdirectory-right" aria-hidden="true"></i>Description → material</span><?php } ?>
                  <?php if ($has_prediction) { ?><span><i class="bx bx-lock-alt" aria-hidden="true"></i>Porosity → &epsilon;, locked</span><?php } ?>
                </p>
              </div>
              <?php if ($has_prediction) { ?>
                <dl class="sim-porosity mb-0">
                  <dt>Predicted porosity</dt>
                  <dd class="empp-readout"><?= number_format((float) $predicted, 2, '.', ''); ?><span class="empp-unit">%</span></dd>
                  <small>&epsilon; = <span class="empp-num"><?= number_format((float) $porosity_fraction, 4, '.', ''); ?></span></small>
                </dl>
              <?php } ?>
            </section>

            <!-- 1. Filter features -->
            <fieldset class="sim-section" id="<?= $sections[1]['id']; ?>" data-section="1">
              <legend>
                <span class="sim-marker" aria-hidden="true">1</span>
                <strong><?= $sections[1]['title']; ?></strong>
                <small><?= $sections[1]['note']; ?></small>
              </legend>
              <div class="row g-3">
                <?php
                $field('fis_material', 'Material', '', [
                  'text' => true,
                  'value' => $material,
                  'placeholder' => 'e.g. PAN',
                  'hint' => $material !== '' ? 'Prefilled from this study\'s description. Edit it if needed.' : '',
                ]);
                $field('fis_porosity', 'Porosity, &epsilon;' . ($has_prediction ? '<i class="bx bx-lock-alt" aria-hidden="true"></i>' : ''), '&ndash;', [
                  'value' => $porosity_fraction,
                  'placeholder' => '0.80',
                  // The study's prediction is the input of record: it cannot be changed here
                  'locked' => $has_prediction,
                  'hint' => $has_prediction
                    ? 'This study\'s predicted porosity (' . number_format((float) $predicted, 2) . '%), as a fraction. It cannot be edited.'
                    : 'Fraction between 0 and 1.',
                ]);
                $field('fis_thickness', 'Thickness, L', 'mm');
                $field('fis_diameter', 'Average fiber diameter, d<sub>f</sub>', '&micro;m');
                ?>
              </div>
            </fieldset>

            <!-- 2. Filter operation -->
            <fieldset class="sim-section" id="<?= $sections[2]['id']; ?>" data-section="2">
              <legend>
                <span class="sim-marker" aria-hidden="true">2</span>
                <strong><?= $sections[2]['title']; ?></strong>
                <small><?= $sections[2]['note']; ?></small>
              </legend>
              <div class="row g-3">
                <?php
                $field('fis_temperature', 'Test temperature, T', '&deg;C', ['placeholder' => '25', 'signed' => true]);
                $field('fis_pressure', 'Test pressure, P', 'mmHg', ['placeholder' => '760']);
                $field('fis_velocity', 'Air face velocity, v<sub>s</sub>', 'm/s');
                $field('fis_area', 'Filter area', 'm&sup2;');
                ?>
              </div>
            </fieldset>

            <!-- 3. Aerosol properties (size classes are fixed at 100: hidden fis_class) -->
            <fieldset class="sim-section" id="<?= $sections[3]['id']; ?>" data-section="3">
              <legend>
                <span class="sim-marker" aria-hidden="true">3</span>
                <strong><?= $sections[3]['title']; ?></strong>
                <small><?= $sections[3]['note']; ?></small>
              </legend>
              <div class="row g-3">
                <?php
                $field('fis_density', 'Particle density, &rho;<sub>p</sub>', 'kg/m&sup3;');
                $field('fis_size_min', 'Minimum particle size, d<sub>pi,min</sub>', '&micro;m', ['hint' => 'Smallest size in the distribution.']);
                $field('fis_size_max', 'Maximum particle size, d<sub>pi,max</sub>', '&micro;m', ['hint' => 'Largest size in the distribution.']);
                $field('fis_concentration', 'Inlet dust concentration, C', 'mg/m&sup3;');
                ?>
              </div>
            </fieldset>
          </div>
        </div>

        <!-- Setup check + run -->
        <div class="col-lg-4">
          <div class="sim-aside">
          <div class="card sim-run">
            <div class="card-body">
              <h5>Setup</h5>
              <p>Fill in the three sections to run the simulation.</p>
              <ol class="sim-check" aria-live="polite">
                <?php foreach ($sections as $n => $s) { ?>
                  <li data-check="<?= $n; ?>">
                    <span class="sim-marker" aria-hidden="true"><?= $n; ?></span>
                    <a href="#<?= $s['id']; ?>"><?= $s['title']; ?></a>
                    <span class="sim-count"></span>
                  </li>
                <?php } ?>
              </ol>
              <p class="sim-outputs-title">What you get</p>
              <ul class="sim-outputs">
                <li><i class="bx bx-check" aria-hidden="true"></i>Particle size distribution</li>
                <li><i class="bx bx-check" aria-hidden="true"></i>Grade and overall collection efficiency</li>
                <li><i class="bx bx-check" aria-hidden="true"></i>Pressure drop and quality factor</li>
              </ul>
              <button type="submit" class="btn btn-primary w-100" id="sim-submit">
                Run simulation <i class="bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
              </button>
              <a href="/dashboard/researcher/research/view/<?= $ree_id; ?>" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            </div>
          </div>

          <!-- Unit converter: helps bring a value to the unit each field asks for.
               Its controls have no name, so nothing here is submitted with the simulation. -->
          <div class="card sim-convert mt-4">
            <div class="card-body">
              <h5>Unit converter</h5>
              <p>Convert a value to the unit a field asks for, then copy it.</p>

              <div class="mb-3">
                <label class="form-label" for="conv-value">Value</label>
                <div class="input-group">
                  <input type="text" inputmode="decimal" data-decimal="signed" class="form-control empp-num" id="conv-value" placeholder="0" autocomplete="off">
                  <select class="form-select conv-unit" id="conv-from" aria-label="From unit"></select>
                </div>
              </div>

              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="form-label mb-1" for="conv-to">Convert to</label>
                  <button type="button" class="conv-swap" id="conv-swap" aria-label="Swap the units"><i class="bx bx-transfer-alt" aria-hidden="true"></i>Swap</button>
                </div>
                <select class="form-select" id="conv-to"></select>
              </div>

              <div class="conv-result" aria-live="polite">
                <div class="conv-result-value">
                  <span class="empp-num" id="conv-out">&ndash;</span>
                  <span class="conv-result-unit" id="conv-out-unit"></span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary conv-copy" id="conv-copy" disabled>
                  <i class="bx bx-copy" aria-hidden="true"></i><span>Copy</span>
                </button>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!-- / Content -->

<script>
// Setup check: a section's marker fills once every one of its editable fields
// holds a value; the run panel mirrors it with a count per section
(function () {
  const form = document.getElementById('sim-form');
  if (!form) return;
  const sections = form.querySelectorAll('.sim-section');

  function update() {
    sections.forEach(function (sec) {
      const n = sec.getAttribute('data-section');
      const fields = Array.prototype.filter.call(
        sec.querySelectorAll('input.form-control'),
        function (el) { return !el.disabled; }
      );
      const filled = fields.filter(function (el) { return el.value.trim() !== ''; }).length;
      const complete = filled === fields.length;
      const check = document.querySelector('[data-check="' + n + '"]');
      [sec, check].forEach(function (el) {
        el.classList.toggle('is-complete', complete);
        el.classList.toggle('is-started', filled > 0 && !complete);
      });
      [sec.querySelector('.sim-marker'), check.querySelector('.sim-marker')].forEach(function (m) {
        m.innerHTML = complete ? '<i class="bx bx-check"></i>' : n;
      });
      check.querySelector('.sim-count').textContent = filled + '/' + fields.length;
    });
  }

  form.addEventListener('input', update);
  update();

  // One simulation per click
  form.addEventListener('submit', function (e) {
    const btn = document.getElementById('sim-submit');
    setTimeout(function () {
      // Skip when another handler (the decimal-comma guard) cancelled the submit
      if (!e.defaultPrevented && form.checkValidity()) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Running simulation…';
      }
    }, 0);
  });
})();

// Unit converter. Each unit is its factor to the quantity's SI unit;
// temperature is affine, so it converts through kelvin instead.
(function () {
  const Q = {
    length: { label: 'Length (fiber diameter, particle size, thickness)', from: 'nm', to: 'µm', units: {
      'nm': 1e-9, 'µm': 1e-6, 'mm': 1e-3, 'cm': 1e-2, 'm': 1, 'in': 0.0254 } },
    area: { label: 'Area (filter area)', from: 'cm²', to: 'm²', units: {
      'mm²': 1e-6, 'cm²': 1e-4, 'm²': 1, 'in²': 6.4516e-4, 'ft²': 0.09290304 } },
    density: { label: 'Density (particle density)', from: 'g/cm³', to: 'kg/m³', units: {
      'kg/m³': 1, 'g/cm³': 1000, 'g/mL': 1000, 'g/L': 1, 'lb/ft³': 16.01846337 } },
    temperature: { label: 'Temperature', from: 'K', to: '°C', units: { '°C': 'C', 'K': 'K', '°F': 'F' } },
    pressure: { label: 'Pressure', from: 'kPa', to: 'mmHg', units: {
      'Pa': 1, 'kPa': 1000, 'mmHg': 133.322387415, 'atm': 101325, 'bar': 1e5,
      'mbar': 100, 'psi': 6894.757293, 'mmH₂O': 9.80665, 'inH₂O': 249.08891 } },
    velocity: { label: 'Velocity (air face velocity)', from: 'cm/s', to: 'm/s', units: {
      'm/s': 1, 'cm/s': 0.01, 'm/min': 1 / 60, 'cm/min': 0.01 / 60, 'km/h': 1 / 3.6, 'ft/min': 0.00508 } },
    concentration: { label: 'Mass concentration (inlet dust)', from: 'µg/m³', to: 'mg/m³', units: {
      'µg/m³': 1e-9, 'mg/m³': 1e-6, 'g/m³': 1e-3, 'mg/L': 1e-3, 'µg/L': 1e-6 } },
    flow: { label: 'Volumetric flow rate', from: 'mL/h', to: 'mL/min', units: {
      'µL/min': 1e-9 / 60, 'mL/min': 1e-6 / 60, 'mL/h': 1e-6 / 3600, 'L/min': 1e-3 / 60, 'm³/h': 1 / 3600, 'm³/s': 1 } },
    viscosity: { label: 'Dynamic viscosity', from: 'cP', to: 'Pa·s', units: {
      'Pa·s': 1, 'mPa·s': 1e-3, 'cP': 1e-3, 'P': 0.1, 'µPa·s': 1e-6 } }
  };

  const value = document.getElementById('conv-value');
  const from = document.getElementById('conv-from');
  const to = document.getElementById('conv-to');
  const out = document.getElementById('conv-out');
  const outUnit = document.getElementById('conv-out-unit');
  const copy = document.getElementById('conv-copy');
  if (!from) return;

  // The source unit picks the quantity: every unit, grouped by quantity.
  // An option's value is "quantity|unit" because a symbol may repeat (mg/L).
  Object.keys(Q).forEach(function (k) {
    const group = document.createElement('optgroup');
    group.label = Q[k].label;
    Object.keys(Q[k].units).forEach(function (u) { group.appendChild(new Option(u, k + '|' + u)); });
    from.appendChild(group);
  });

  function current() { return from.value.split('|'); }

  // Target units: only those of the same quantity
  function fillTargets(preferred) {
    const q = current()[0];
    to.innerHTML = '';
    Object.keys(Q[q].units).forEach(function (u) { to.add(new Option(u, u)); });
    const fallback = Q[q].to !== current()[1] ? Q[q].to : Q[q].from;
    to.value = preferred && Q[q].units[preferred] !== undefined ? preferred : fallback;
  }

  function toKelvin(v, u) { return u === 'C' ? v + 273.15 : u === 'F' ? (v - 32) * 5 / 9 + 273.15 : v; }
  function fromKelvin(v, u) { return u === 'C' ? v - 273.15 : u === 'F' ? (v - 273.15) * 9 / 5 + 32 : v; }

  // Six significant figures, plain notation for everyday magnitudes
  function format(n) {
    if (n === 0) return '0';
    const a = Math.abs(n);
    if (a >= 1e-4 && a < 1e9) return String(Number(n.toPrecision(6)));
    return n.toExponential(5).replace(/\.?0+e/, 'e');
  }

  let result = '';
  function convert() {
    const key = current()[0], unit = current()[1];
    const q = Q[key];
    const raw = value.value.trim();
    const v = Number(raw);
    result = '';
    if (raw !== '' && raw !== '-' && isFinite(v)) {
      const r = key === 'temperature'
        ? fromKelvin(toKelvin(v, q.units[unit]), q.units[to.value])
        : v * q.units[unit] / q.units[to.value];
      result = format(r);
    }
    out.textContent = result || '–';
    outUnit.textContent = result ? to.value : '';
    copy.disabled = !result;
  }

  from.addEventListener('change', function () { fillTargets(to.value); convert(); });
  to.addEventListener('change', convert);
  value.addEventListener('input', convert);
  // Enter here must not submit the simulation form around it
  value.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });

  document.getElementById('conv-swap').addEventListener('click', function () {
    const q = current()[0], f = current()[1];
    from.value = q + '|' + to.value;
    to.value = f;
    convert();
  });

  copy.addEventListener('click', function () {
    if (!result) return;
    const done = function () {
      const label = copy.querySelector('span');
      const icon = copy.querySelector('i');
      copy.classList.add('is-copied');
      label.textContent = 'Copied';
      icon.className = 'bx bx-check';
      setTimeout(function () {
        copy.classList.remove('is-copied');
        label.textContent = 'Copy';
        icon.className = 'bx bx-copy';
      }, 1600);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(result).then(done);
    } else {
      // Fallback for plain http
      const tmp = document.createElement('textarea');
      tmp.value = result;
      tmp.setAttribute('readonly', '');
      tmp.style.position = 'fixed';
      tmp.style.opacity = '0';
      document.body.appendChild(tmp);
      tmp.select();
      document.execCommand('copy');
      document.body.removeChild(tmp);
      done();
    }
  });

  from.value = 'length|nm';
  fillTargets('µm');
  convert();
})();
</script>
