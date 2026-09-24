<?php
// Older records may hold comma decimals ("0,700"); the math below needs dots.
$empp_numeric = ['fis_thickness', 'fis_diameter', 'fis_porosity', 'fis_temperature', 'fis_pressure', 'fis_velocity',
                 'fis_area', 'fis_density', 'fis_size_min', 'fis_size_max', 'fis_concentration', 'fis_class'];
foreach ($empp_numeric as $empp_f) {
  $this->getView()->filter_simulation->__set($empp_f, str_replace(',', '.', (string) $this->getView()->filter_simulation->__get($empp_f)));
}
// Inputs outside the model's domain would crash the calculation (log of 0, division by zero)
$empp_fs = $this->getView()->filter_simulation;
$empp_invalid = [];
foreach ($empp_numeric as $empp_f) {
  if (!is_numeric($empp_fs->__get($empp_f))) $empp_invalid[] = "$empp_f is not a number (\"" . $empp_fs->__get($empp_f) . "\")";
}
if (!$empp_invalid) {
  if ($empp_fs->fis_porosity <= 0 || $empp_fs->fis_porosity >= 1) $empp_invalid[] = "Porosity must be a fraction between 0 and 1 (got {$empp_fs->fis_porosity})";
  if ($empp_fs->fis_size_min <= 0) $empp_invalid[] = "Minimum particle size must be greater than 0 (got {$empp_fs->fis_size_min})";
  if ($empp_fs->fis_size_max <= $empp_fs->fis_size_min) $empp_invalid[] = "Maximum particle size must be greater than the minimum (got {$empp_fs->fis_size_max})";
  foreach (['fis_thickness' => 'Thickness', 'fis_diameter' => 'Fiber diameter', 'fis_velocity' => 'Air face velocity', 'fis_pressure' => 'Pressure', 'fis_class' => 'Size classes'] as $empp_f => $empp_label) {
    if ($empp_fs->__get($empp_f) <= 0) $empp_invalid[] = "$empp_label must be greater than 0 (got {$empp_fs->__get($empp_f)})";
  }
}
// Derived values are computed when the simulation is saved; records saved with
// comma decimals got them wrong (e.g. Kuwabara = 0) and must be re-run.
$empp_sim = $this->getView()->simulation;
if (!$empp_invalid && (!$empp_sim || (float) $empp_sim->sim_kuwabara == 0.0 || (float) $empp_sim->sim_knudsen == 0.0 || (float) $empp_sim->sim_permeability_k1 == 0.0)) {
  $empp_invalid[] = 'The derived properties saved with this simulation are invalid (it was probably saved with comma decimals). Run it again.';
}
if ($empp_invalid) { ?>
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <a href="/dashboard/researcher/research/view/<?= (int) $empp_fs->fk_research_ree_id; ?>" class="d-inline-flex align-items-center mb-2">
        <i class="bx bx-chevron-left"></i> Back to the study
      </a>
      <span class="empp-eyebrow">Filtration performance simulation</span>
      <h4 class="mb-4">This simulation can't be calculated</h4>
      <div class="alert alert-warning">
        <strong>Some of the saved inputs are outside the model's valid range:</strong>
        <ul class="mb-0 mt-2"><?php foreach ($empp_invalid as $empp_msg) echo '<li>' . htmlspecialchars($empp_msg) . '</li>'; ?></ul>
      </div>
      <a class="btn btn-primary" href="/dashboard/researcher/research/filter-simulation-create/<?= (int) $empp_fs->fk_research_ree_id; ?>">Set up a new simulation</a>
    </div>
<?php
  return;
}

$material = $this->getView()->filter_simulation->fis_material; //Rounded values
$thickness = $this->getView()->filter_simulation->fis_thickness; //Rounded values
$diameter = round($this->getView()->filter_simulation->fis_diameter, 2); //Rounded values
$porosity = round($this->getView()->filter_simulation->fis_porosity, 3); //Rounded values
$temperature = $this->getView()->filter_simulation->fis_temperature; //Rounded values
$pressure = $this->getView()->filter_simulation->fis_pressure; //Rounded values
$velocity = round($this->getView()->filter_simulation->fis_velocity, 3); //Rounded values
$area = $this->getView()->filter_simulation->fis_area; //Rounded values
$density = round($this->getView()->filter_simulation->fis_density, 3); //Rounded values
$min_particle_size = $this->getView()->filter_simulation->fis_size_min; //Rounded values
$max_particle_size = $this->getView()->filter_simulation->fis_size_max; //Rounded values
$concentration = $this->getView()->filter_simulation->fis_concentration; //Rounded values
$class = $this->getView()->filter_simulation->fis_class; //Rounded values

$sim_density = $this->getView()->simulation->sim_density; //Rounded values
$sim_permeability_k1 = sprintf("%.2E", $this->getView()->simulation->sim_permeability_k1); //Rounded values
$sim_permeability_k2 = sprintf("%.2E", $this->getView()->simulation->sim_permeability_k2); //Rounded values
$sim_gravitational = round($this->getView()->simulation->sim_gravitational, 1); //Rounded values
$sim_air_density = round($this->getView()->simulation->sim_air_density, 3); //Rounded values
$sim_air_viscosity = sprintf("%.2E", $this->getView()->simulation->sim_air_viscosity); //Rounded values
$sim_air_free_path = sprintf("%.2E", $this->getView()->simulation->sim_air_free_path); //Rounded values
$sim_test_pressure = round($this->getView()->simulation->sim_test_pressure, 0); //Rounded values
$sim_boltzmann = sprintf("%.2E", $this->getView()->simulation->sim_boltzmann); //Rounded values
$sim_kuwabara = round($this->getView()->simulation->sim_kuwabara, 4); //Rounded values
$sim_knudsen = round($this->getView()->simulation->sim_knudsen, 5); //Rounded values

$material_a = $this->getView()->filter_simulation->fis_material; //Absolute values
$thickness_a = $this->getView()->filter_simulation->fis_thickness; //Absolute values
$diameter_a = $this->getView()->filter_simulation->fis_diameter; //Absolute values
$porosity_a = $this->getView()->filter_simulation->fis_porosity; //Absolute values
$temperature_a = $this->getView()->filter_simulation->fis_temperature; //Absolute values
$pressure_a = $this->getView()->filter_simulation->fis_pressure; //Absolute values
$velocity_a = $this->getView()->filter_simulation->fis_velocity; //Absolute values
$area_a = $this->getView()->filter_simulation->fis_area; //Absolute values
$density_a = $this->getView()->filter_simulation->fis_density; //Absolute values
$min_particle_size_a = $this->getView()->filter_simulation->fis_size_min; //Absolute values
$max_particle_size_a = $this->getView()->filter_simulation->fis_size_max; //Absolute values
$concentration_a = $this->getView()->filter_simulation->fis_concentration; //Absolute values
$class_a = $this->getView()->filter_simulation->fis_class; //Absolute values

$sim_density_a = $this->getView()->simulation->sim_density; //Absolute values
$sim_permeability_k1_a = $this->getView()->simulation->sim_permeability_k1; //Absolute values
$sim_permeability_k2_a = $this->getView()->simulation->sim_permeability_k2; //Absolute values
$sim_gravitational_a = $this->getView()->simulation->sim_gravitational; //Absolute values
$sim_air_density_a = $this->getView()->simulation->sim_air_density; //Absolute values
$sim_air_viscosity_a = $this->getView()->simulation->sim_air_viscosity; //Absolute values
$sim_air_free_path_a = $this->getView()->simulation->sim_air_free_path; //Absolute values
$sim_test_pressure_a = $this->getView()->simulation->sim_test_pressure; //Absolute values
$sim_boltzmann_a = $this->getView()->simulation->sim_boltzmann; //Absolute values
$sim_kuwabara_a = $this->getView()->simulation->sim_kuwabara; //Absolute values
$sim_knudsen_a = $this->getView()->simulation->sim_knudsen; //Absolute values

$a = round(log(100000000 / 1.00000001) / log($max_particle_size / $min_particle_size), 1);
$a_a = log(100000000 / 1.00000001) / log($max_particle_size_a / $min_particle_size_a); //Absolute values

$dm = round($max_particle_size / pow(log(1 / 0.00000001), (1 / $a)), 1);
$dm_a = $max_particle_size_a / pow(log(1 / 0.00000001), (1 / $a_a)); //Absolute values

$delta_dpi = round(($max_particle_size - $min_particle_size) / $class, 1);
$delta_dpi_a = ($max_particle_size_a - $min_particle_size_a) / $class_a; //Absolute values

$wi_discrete_sum = 0;
$wi_discrete_sum_a = 0; //Absolute values

$wi_dpi_sum = 0;
$wi_dpi_sum_a = 0; //Absolute values

$dpi_data = [];
$dpi_data_a = []; //Absolute values
$wi_discrete_data = [];
$wi_discrete_data_a = []; //Absolute values
$wi_accumulated_data = [];
$wi_accumulated_data_a = []; //Absolute values
$wi_discrete_norm_data = [];
$wi_discrete_norm_data_a = []; //Absolute values



for ($x = 0; $x <= $class; $x++) {

  if ($x == 0) {
    $dpi = htmlspecialchars(dpi($min_particle_size, $max_particle_size, $x, $class));
    $dpi_a = htmlspecialchars(dpi($min_particle_size_a, $max_particle_size_a, $x, $class_a));
  } else {
    $dpi = round(htmlspecialchars(dpi($min_particle_size, $max_particle_size, $x, $class)), 3);
    $dpi_a = round(htmlspecialchars(dpi($min_particle_size_a, $max_particle_size_a, $x, $class_a)), 3);
  }

  array_push($dpi_data, $dpi);
  array_push($dpi_data_a, $dpi_a); //Absolute values

  if ($x == 0) {
    $wi_discrete = wiDiscrete($a, $dm, $dpi, $delta_dpi);
    $wi_discrete_a = wiDiscrete($a, $dm, $dpi_a, $delta_dpi); //Absolute values

  } else {
    $wi_discrete = wiDiscrete($a, $dm, $dpi, $delta_dpi);
    $wi_discrete_a = wiDiscrete($a, $dm, $dpi_a, $delta_dpi); //Absolute values
  }
  array_push($wi_discrete_data, $wi_discrete);
  array_push($wi_discrete_data_a, $wi_discrete_a); //Absolute values

  $wi_discrete_sum += round($wi_discrete, 4);
  $wi_discrete_sum_a += $wi_discrete_a; //Absolute values

  if ($x == 0) {
    $wi_dpi_sum += $dpi / $wi_discrete;
    $wi_dpi_sum_a += 0;
  } else {
    $wi_dpi_sum += round(($dpi / $wi_discrete), 4);
    $wi_dpi_sum_a += dpi($min_particle_size_a, $max_particle_size_a, $x, $class_a) / $wi_discrete_a;
  }
  array_push($wi_accumulated_data, $wi_dpi_sum);
  array_push($wi_accumulated_data_a, $wi_dpi_sum_a); //Absolute values

  /* $wi_discrete_norm_ = htmlspecialchars($wi_discrete / $wi_discrete_sum);
  array_push($wi_discrete_norm_data, $wi_discrete_norm_); */
}
for ($x = 0; $x <= $class; $x++) {
  $dpi = round(htmlspecialchars(dpi($min_particle_size, $max_particle_size, $x, $class)), 3);
  $dpi_a = dpi($min_particle_size_a, $max_particle_size_a, $x, $class_a); //Absolute values

  $wi_discrete = round(htmlspecialchars(wiDiscrete($a, $dm, $dpi, $delta_dpi)), 4);
  $wi_discrete_a = wiDiscrete($a, $dm, $dpi_a, $delta_dpi_a); //Absolute values

  $wi_discrete_norm = round(htmlspecialchars($wi_discrete / $wi_discrete_sum), 4);
  $wi_discrete_norm_a = $wi_discrete_a / $wi_discrete_sum_a; //Absolute values
  array_push($wi_discrete_norm_data, $wi_discrete_norm);
  array_push($wi_discrete_norm_data_a, $wi_discrete_norm_a); //Absolute values
}
$dsauter = 1 / (floatval($wi_dpi_sum_a) * 100000000000);

/* var_dump($wi_dpi_sum_a);
var_dump(floatval($wi_dpi_sum_a));
var_dump(floatval($wi_dpi_sum_a) * 100000000000);
var_dump($dsauter);
exit; */

$wi_accumulated = 0;

function dpi($dpi_min, $dpi_max, $terms_n, $class)
{
  return exp(log($dpi_min) + $terms_n * ((log($dpi_max) - log($dpi_min)) / $class));
}

function wiDiscrete($a, $dm, $dpi, $delta_dpi)
{
  return $a / $dm * pow(($dpi / $dm), ($a - 1)) * exp(-pow(($dpi / $dm), $a)) * $delta_dpi;
}

function fs($dpi_fs, $sim_air_free_path)
{
  return 1 + ($sim_air_free_path / ($dpi_fs / 1000000)) * (2.33 + 0.966 * exp(-0.4985 * ($dpi_fs / 1000000) / $sim_air_free_path));
}

function dms($dpi_fs, $sim_boltzmann, $temperature, $fs, $sim_air_viscosity)
{
  return $sim_boltzmann * ($temperature + 273) * ($fs / (3 * pi() * $sim_air_viscosity * ($dpi_fs / 1000000)));
}

function pe($diameter, $velocity, $dms)
{
  return ($diameter / 1000000) * $velocity / $dms;
}

function cd($sim_knudsen, $porosity, $pe, $sim_kuwabara)
{

  return 1 + 0.388 * $sim_knudsen * pow(($porosity * $pe / $sim_kuwabara), (1 / 3));
}

function nd($porosity, $sim_kuwabara, $pe, $cd)
{
  return 1.6 * pow(($porosity / $sim_kuwabara), (1 / 3)) * pow($pe, (-2 / 3)) * $cd;
}



function r($dpi, $diameter)
{
  return $dpi / $diameter;
}

function cr($sim_knudsen, $r)
{

  return 1 + 1.996 * $sim_knudsen / $r;
}

function ndi($porosity, $sim_kuwabara, $r, $cr)
{
  $expressao = 0.6 * ($porosity / $sim_kuwabara) * pow($r, 2) / (1 + $r) * $cr;
  return ($expressao < 1) ? $expressao : 1;
}

function stk($density, $velocity, $fs, $dpi, $sim_air_viscosity, $diameter)
{

  $num = $density * $velocity * $fs * pow(($dpi / 1000000), 2);
  $den = 9 * $sim_air_viscosity * ($diameter / 1000000);


  return $num / $den;
}

function ni($stk)
{

  $a = 0.77;
  $b = 0.22;

  $num = pow($stk, 3);
  $den = pow($stk, 3) + $a * pow($stk, 2) + $b;


  return $num / $den;
}

function ntotal($nd, $ndi, $ni)
{
  return 1 - (1 - $nd) * (1 - $ndi) * (1 - $ni);
}

function egrade($ntotal, $sim_density, $thickness, $diameter)
{
  return 1 - exp(-4 * $ntotal * $sim_density / (1 - $sim_density) * ($thickness / 1000) / ($diameter / 1000000));
}

function eoveral($egrade, $wi)
{
  return $egrade * $wi;
}

function mpps(array $search_array, array $min_value_array): ?float
{
  // 1. Find the minimum value in the array
  if (empty($min_value_array)) {
    return null;
  }
  $min_value = min($min_value_array);

  // 2. Find the position (index) of the minimum value
  $position = array_search($min_value, $min_value_array, true); // The 'true' ensures strict search

  // 3. Check if the position was found and if it exists in the search array
  if ($position === false || !isset($search_array[$position])) {
    return null; // Return null if the position is not found or the index doesn't exist
  }

  // 4. Get the corresponding value and multiply it by 1000
  $corresponding_value = $search_array[$position];

  return (float) $corresponding_value * 1000;
}


function minimum_grade_efficiency(array $data_array): ?float
{
  // Check if the array is empty to prevent errors.
  if (empty($data_array)) {
    return null;
  }

  // 1. Find the minimum value in the array using the min() function.
  $min_value = min($data_array);

  // 2. Multiply the minimum value by 100.
  $result = $min_value * 100;

  return (float) $result;
}

function overall_filter_efficiency($value): ?float
{
  // Check if the array is empty to prevent errors.
  if (empty($value)) {
    return null;
  }


  // 2. Multiply the minimum value by 100.
  $result = $value * 100;

  return (float) $result;
}

function flow_rate(float $velocity, float $filter_area): float
{
  // Multiply the two values by 1000 and 60
  return $velocity * $filter_area * 1000 * 60;
}

function pressure_drop(float $sim_air_viscosity_a, float $sim_permeability_k1_a, float $velocity_a, float $sim_density_a, float $sim_permeability_k2_a, float $thickness_a): float
{
  // The core calculation, separated into two parts for clarity.
  $part1 = ($sim_air_viscosity_a / $sim_permeability_k1_a * $velocity_a);
  $part2 = ($sim_density_a / $sim_permeability_k2_a * pow($velocity_a, 2));

  // The final result.
  return ($part1 + $part2) * ($thickness_a / 1000);
}



function viscous_contribution(float $sim_air_viscosity_a, float $sim_permeability_k1_a, float $velocity_a, float $thickness_a, float $pressure_drop): float
{
  // Check if I31 is zero to prevent a division-by-zero error.
  if ($pressure_drop == 0) {
    // You can return 0, throw an exception, or return null depending on your needs.
    // Returning 0 is often a safe choice for this type of calculation.
    return 0.0;
  }

  // The calculation, broken down for clarity.
  $part1 = ($sim_air_viscosity_a / $sim_permeability_k1_a * $velocity_a);
  $part2 = ($thickness_a / 1000);
  $part3 = $pressure_drop;

  $result = ($part1 * $part2) / $part3 * 100;

  return (float) $result;
}

function inertial_contribution(float $viscous_contribution): float
{
  return 100 - $viscous_contribution;
}

function quality_factor(float $overvall_filter_efficiency, float $pressure_drop): ?float
{
  // 1. Validate the input for the natural logarithm (LN).
  // The argument for LN must be greater than 0. 
  // If overvall_filter_efficiency is 100, the argument (1 - 100/100) will be 0, which is undefined for LN.
  $ln_argument = 1 - ($overvall_filter_efficiency / 100);
  if ($ln_argument <= 0) {
    return null; // Return null for invalid input.
  }

  // 2. Validate the denominator to prevent a division-by-zero error.
  if ($pressure_drop == 0) {
    return null; // Return null if pressure_drop is zero.
  }

  // 3. Perform the calculation.
  $numerator = -log($ln_argument); // The natural logarithm in PHP is log()
  $result = $numerator / $pressure_drop;

  return (float) $result;
}

function outlet_dust_concentration(float $concentration_a, float $overall_filter_efficiency): float
{
  // The calculation, as per the Excel formula.
  $result = $concentration_a * (1 - ($overall_filter_efficiency / 100));

  return (float) $result;
}


/* var_dump(round($wi_discrete_sum,4));
exit;  */
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"
  integrity="sha384-jb8JQMbMoBUzgWatfe6COACi2ljcDdZQ2OxczGA3bGNeWe+6DChMTBJemed7ZnvJ" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.1.0/dist/chartjs-plugin-annotation.min.js"
  integrity="sha384-3N9GHhCtN3CQef6tNfqgZlv7sQLYIkcChN+uaTZ7xVdzKYp/SjBNPxa92+hM7EAY" crossorigin="anonymous"></script>
<style>
  .empp-data-table {
    width: auto;
    min-width: 100%;
    margin: 0;
    font-size: 0.8125rem;
  }
  .empp-data-table th,
  .empp-data-table td {
    padding: 0.4rem 0.6rem;
    border-color: var(--empp-line);
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
  }
  .empp-data-table td {
    font-family: var(--empp-mono);
    color: var(--empp-ink);
  }
  .empp-data-table thead th,
  .empp-data-table thead td {
    position: sticky;
    top: 0;
    background: var(--empp-surface);
    font-family: var(--empp-font);
    text-transform: none;
    letter-spacing: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--empp-text);
  }
  .empp-data-table tr.table-striped td {
    background: var(--empp-bg);
  }
  .empp-data-table .sum-row td {
    font-weight: 600;
    border-top: 2px solid var(--empp-line-strong);
  }
  .table-scroll {
    max-height: 560px;
    overflow: auto;
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius);
  }

  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: var(--empp-line);
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius);
    overflow: hidden;
  }
  .kpi-grid > div {
    background: var(--empp-surface);
    padding: 1rem 1.125rem;
  }
  .kpi-grid .empp-readout {
    font-size: 1.625rem;
    margin-top: 0.25rem;
  }
  .kpi-grid .kpi-hero .empp-readout {
    font-size: 2.25rem;
  }
  .kpi-label {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
  }
  .kpi-info {
    flex-shrink: 0;
    margin: -0.3rem -0.4rem 0 0;
    padding: 0.25rem;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: var(--empp-muted);
    font-size: 1.125rem;
    line-height: 1;
    cursor: pointer;
    transition: color 0.15s, background-color 0.15s;
  }
  .kpi-info:hover,
  .kpi-info:focus-visible {
    color: var(--empp-primary);
    background: var(--empp-primary-soft);
  }
  .kpi-info:focus-visible {
    outline: 2px solid rgba(var(--empp-primary-rgb), 0.45);
    outline-offset: 1px;
  }
  /* Explanation dialog: wide, two columns, compact icon, so it fits without scrolling */
  .kpi-popup.swal2-popup {
    padding: 1.5rem 1.75rem 1.5rem;
  }
  .kpi-popup .swal2-title {
    padding-top: 0;
    font-size: 1.5rem;
  }
  .kpi-popup .swal2-html-container {
    margin: 0.75rem 0.5rem 0;
  }
  .kpi-popup .swal2-actions {
    margin-top: 1rem;
  }
  .kpi-explainer {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 0 2rem;
  }
  .kpi-explainer .kpi-col > h6.mt-0 {
    margin-top: 0;
  }
  /* Short viewports (small laptops): tighter type and spacing so it still fits */
  @media (max-height: 700px) and (min-width: 992px) {
    .kpi-popup.swal2-popup { padding: 1rem 1.5rem 1rem; }
    .kpi-popup .swal2-title { font-size: 1.25rem; }
    .kpi-popup .swal2-html-container { margin-top: 0.5rem; }
    .kpi-popup .swal2-actions { margin-top: 0.625rem; }
    .kpi-popup .swal2-confirm { padding: 0.375rem 1.25rem; }
    .kpi-popup .kpi-explainer { font-size: 0.8438rem; line-height: 1.45; }
    .kpi-popup .kpi-explainer h6 { margin-top: 0.625rem; }
    .kpi-popup .kpi-verdict { padding: 0.5rem 0.75rem; }
    .kpi-popup .kpi-verdict-value { font-size: 1rem; }
    .kpi-popup .kpi-bands { font-size: 0.75rem; }
    .kpi-popup .kpi-bands td { padding: 0.15rem 0.5rem; }
    .kpi-popup .kpi-explainer .kpi-formula { padding: 0.4rem 0.625rem; font-size: 0.8125rem; }
    .kpi-popup .kpi-disclaimer { margin-top: 0.625rem !important; }
  }
  @media (max-width: 991.98px) {
    .kpi-popup.swal2-popup {
      width: 94% !important;
    }
    .kpi-explainer {
      grid-template-columns: 1fr;
    }
    .kpi-explainer .kpi-col > h6.mt-0 {
      margin-top: 1rem;
    }
  }
  .kpi-explainer {
    text-align: left;
    font-size: 0.9375rem;
    line-height: 1.55;
    color: var(--empp-text);
  }
  .kpi-explainer h6 {
    margin: 1rem 0 0.25rem;
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--empp-muted);
  }
  .kpi-explainer p {
    margin: 0;
  }
  /* Verdict for this simulation, coloured by level */
  .kpi-verdict {
    --kpi-c: var(--empp-muted);
    --kpi-bg: var(--empp-bg);
    padding: 0.75rem 0.875rem;
    border-radius: var(--empp-radius-sm);
    border-left: 4px solid var(--kpi-c);
    background: var(--kpi-bg);
  }
  .kpi-good     { --kpi-c: #1f7a5a; --kpi-bg: #e9f5ef; }
  .kpi-moderate { --kpi-c: #a86400; --kpi-bg: #fdf3e2; }
  .kpi-poor     { --kpi-c: #b3261e; --kpi-bg: #fcecea; }
  .kpi-neutral  { --kpi-c: #6a7284; --kpi-bg: #f3f4f6; }
  .kpi-verdict-head {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem 0.75rem;
    margin-bottom: 0.375rem;
  }
  .kpi-verdict-value {
    font-family: var(--empp-mono);
    font-size: 1.125rem;
    font-weight: 500;
    color: var(--empp-ink);
  }
  .kpi-badge {
    padding: 0.125rem 0.5rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #fff;
    background: var(--kpi-c);
  }
  .kpi-bands {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.8125rem;
    border-collapse: collapse;
  }
  .kpi-bands td {
    padding: 0.25rem 0.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    color: var(--empp-muted);
  }
  .kpi-bands td:first-child {
    font-family: var(--empp-mono);
    white-space: nowrap;
    width: 1%;
  }
  .kpi-bands tr.is-current td {
    color: var(--empp-ink);
    background: var(--kpi-bg);
  }
  .kpi-col > .kpi-read:first-child {
    margin-top: 0;
  }
  .kpi-bands tr.is-current td:first-child {
    box-shadow: inset 3px 0 0 var(--kpi-c);
  }
  .kpi-disclaimer {
    margin-top: 1rem !important;
    font-size: 0.75rem;
    color: var(--empp-muted);
  }

  .kpi-explainer .kpi-formula {
    padding: 0.625rem 0.75rem;
    border-radius: var(--empp-radius-sm);
    background: var(--empp-bg);
    font-size: 0.875rem;
  }

  .kpi-grid small {
    display: block;
    margin-top: 0.25rem;
    color: var(--empp-muted);
    font-size: 0.75rem;
  }

  .props-list {
    display: grid;
    grid-template-columns: 1fr auto;
    margin: 0;
    border-top: 1px solid var(--empp-line);
  }
  .props-list dt:hover,
  .props-list dt:hover + dd,
  .props-list dt:has(+ dd:hover),
  .props-list dd:hover {
    background: var(--empp-bg);
  }
  /* Each name/value pair sits on a ruled row so the eye can follow it across */
  .props-list dt,
  .props-list dd {
    padding: 0.5rem 0.5rem;
    border-bottom: 1px solid var(--empp-line);
  }
  .props-list dt {
    font-weight: 400;
    color: var(--empp-muted);
    font-size: 0.8125rem;
  }
  .props-list dd {
    margin: 0;
    padding-left: 1rem;
    text-align: right;
    font-family: var(--empp-mono);
    font-variant-numeric: tabular-nums;
    color: var(--empp-ink);
    font-size: 0.875rem;
  }

  .chart-box {
    position: relative;
    height: 380px;
  }
  .chart-note {
    font-size: 0.8125rem;
    color: var(--empp-muted);
    margin-bottom: 0.75rem;
  }
  @media (max-width: 991.98px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  }
  .sim-tabs {
    border-bottom: 1px solid var(--empp-line);
  }
  .sim-tabs .nav-link {
    border: 0;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0.75rem 0.25rem;
    margin-right: 1.5rem;
    font-weight: 500;
    color: var(--empp-muted);
  }
  .sim-tabs .nav-link:hover {
    color: var(--empp-ink);
  }
  .sim-tabs .nav-link.active {
    color: var(--empp-primary);
    border-bottom-color: var(--empp-primary);
  }
</style>

<?php
/* ---------------------------------------------------------------------------
 * The tables below also compute the arrays used by the results and charts
 * ($egrade_data, $eoveral_sum, $nd_data, ...), so they run first, in their
 * original order, and their HTML is captured to be placed in the tabs.
 * ------------------------------------------------------------------------- */
ob_start(); ?>
                    <table class="table empp-data-table">
                      <thead>
                        <tr>
                          <td colspan="2" style="text-align: center; font-weight: bold;">Size range</td>
                          <td colspan="2" style="text-align: center; font-weight: bold;">Average values</td>
                        </tr>
                      </thead>
                      <tr>
                        <td style="text-align: center; font-weight: bold;">dpi<sub>min</sub> (µm)</td>
                        <td><?= htmlspecialchars($min_particle_size) ?></td>
                        <td style="text-align: center; font-weight: bold;">d<sub>sauter</sub> (µm)</td>
                        <td><?php
                            // 1. Format the number in scientific notation with 2 decimal places.
                            // The result is the string: "3.51e-22"
                            $formatted_string = sprintf('%.2e', $dsauter);

                            // 2. Split the string at the 'e' character to isolate the numeric part.
                            $parts = explode('e', $formatted_string);

                            // 3. The first part of the array contains the number you need.
                            $only_number = $parts[0];

                            echo $only_number; // Output format: 3.51
                            ?></td>
                      </tr>
                      <tr>
                        <td style="text-align: center; font-weight: bold;">dpi<sub>max</sub> (µm)</td>
                        <td><?= htmlspecialchars($max_particle_size) ?></td>
                        <td style="text-align: center; font-weight: bold;">d<sub>50</sub> (mm)</td>
                        <td><?= number_format($dm * pow(log(1 / 0.5), (1 / $a)), 2, '.', '') ?></td>
                      </tr>
                      <tr>
                        <td style="text-align: center; font-weight: bold;">d<sub>m</sub> (µm)</td>
                        <td><?= $dm ?></td>
                        <td style="text-align: center; font-weight: bold;">d<sub>90</sub> (mm)</td>
                        <td><?= number_format(round($dm * pow(log(1 / 0.1), (1 / $a)), 1), 2, '.', '') ?></td>
                      </tr>
                      <tr>
                        <td style="text-align: center; font-weight: bold;">a (-)</td>
                        <td><?= $a ?></td>
                        <td style="text-align: center; font-weight: bold;">d<sub>10</sub> (mm)</td>
                        <td><?= number_format($a * pow(log(1 / 0.9), (1 / $wi_discrete_sum)), 2, '.', '') ?></td>
                      </tr>
                      <tr>
                        <td style="text-align: center; font-weight: bold;">sum</td>
                        <td><?= htmlspecialchars(round($wi_discrete_sum, 4)) ?></td>
                        <td style="text-align: center; font-weight: bold;">&#916;d<sub>pi</sub>(µm)</td>
                        <td><?= number_format($delta_dpi, 2, '.', '') ?></td>
                      </tr>
                      <tr>
                        <td colspan="2" style="text-align: center; font-weight: bold;">Class</td>
                        <td colspan="2" style="text-align: center;"><?= htmlspecialchars($class) ?></td>

                      </tr>
                    </table>

<?php $html_distribution_summary = ob_get_clean();

ob_start(); ?>
                      <table class="table empp-data-table">
                        <thead>
                          <tr>
                            <th style="text-align: center; font-weight: bold;">Terms, n</th>
                            <th style="text-align: center; font-weight: bold;">d<sub>pi</sub> (µm)</th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i discrete</sub></th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i discrete, norm</sub></th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i</sub> / d<sub>pi</sub> [1 / µm]</th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i</sub> accumulated</th>
                          </tr>
                        </thead>
                        <tbody>

                          <?php for ($x = 0; $x <= $class; $x++) { ?>
                            <tr class="<?= $x % 2 != 0 ? 'table-striped' : '' ?>">
                              <td style="text-align: center;"><?= htmlspecialchars($x) ?></td>
                              <td style="text-align: center;"><?php
                                                              $dpi_c = htmlspecialchars(dpi($min_particle_size, $max_particle_size, $x, $class));
                                                              echo number_format(round($dpi_c, 3), 3, '.', '');
                                                              //echo floor($dpi * pow(10, 3)) / pow(10, 3);
                                                              ?>
                              </td>
                              <td style="text-align: center;"><?php
                                                              $wi_discrete_c = htmlspecialchars(wiDiscrete($a, $dm, $dpi_c, $delta_dpi));
                                                              echo number_format(round($wi_discrete_c, 4), 4, '.', '');
                                                              ?>
                              </td>
                              <td style="text-align: center;">
                                <?php

                                $wi_discrete_norm_c = $wi_discrete_c / $wi_discrete_sum;
                                echo number_format(round($wi_discrete_norm_c, 4), 4, '.', '');
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                if ($x != 0) {
                                  $wi_dpi_c = htmlspecialchars($wi_discrete_c / $dpi_c);
                                  echo sprintf('%.2E', $wi_dpi_c);
                                }
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                if ($x == 0) {
                                  $wi_accumulated = round($wi_discrete_norm_c, 6);
                                } else {
                                  $wi_accumulated += round($wi_discrete_norm_c, 6);
                                }
                                echo number_format($wi_accumulated, 4, '.', '');
                                ?>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                        <tbody>
                          <tr class="sum-row">
                            <td colspan="2" style="text-align: center; font-weight: bold;">SUM</td>
                            <td style="text-align: center;"><?= htmlspecialchars(round($wi_discrete_sum_a, 4)) ?></td>
                            <td style="text-align: center;"><?= number_format(ceil(array_sum($wi_discrete_norm_data_a)), 4, '.', '') ?></td>
                            <td style="text-align: center;"><?= number_format(array_sum($wi_accumulated_data_a) / 100000000000, 4, ',', '') ?></td>
                            <td></td>
                          </tr>
                        </tbody>

                      </table>

<?php $html_distribution_table = ob_get_clean();

ob_start(); ?>
                      <table class="table empp-data-table">
                        <thead>
                          <tr>
                            <th style="text-align: center; font-weight: bold;">d<sub>pi</sub> (µm)</th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i discrete, norm</sub></th>
                            <th style="text-align: center; font-weight: bold;">W<sub>i</sub> accumulated</th>
                            <th style="text-align: center; font-weight: bold;">F<sub>s</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">D(m<sup>2</sup>) (-)</th>
                            <th style="text-align: center; font-weight: bold;">P<sub>e</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">C<sub>d</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">&eta;<sub>o</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">R (-)</th>
                            <th style="text-align: center; font-weight: bold;">C<sub>r</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">&eta;<sub>DI</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">S<sub>TK</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">&eta;<sub>I</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">&eta;<sub>Total</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">E<sub>grade</sub> (-)</th>
                            <th style="text-align: center; font-weight: bold;">E<sub>overal</sub></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $wi_acc_s = 0;
                          $eoveral_sum = [];
                          $egrade_data = [];
                          $wi_discrete_norm_a_data = [];
                          $ntotal_data = [];
                          $ndi_data = [];
                          $ni_data = [];
                          $nd_data = [];
                          for ($x = 0; $x <= $class; $x++) { ?>
                            <tr class="<?= $x % 2 != 0 ? 'table-striped' : '' ?>">
                              <td style="text-align: center;"><?php
                                                              $dpi_a = dpi($min_particle_size_a, $max_particle_size_a, $x, $class_a);
                                                              $dpi_s = round(dpi($min_particle_size, $max_particle_size, $x, $class), 3);
                                                              echo number_format($dpi_s, 3, '.', '');
                                                              ?>
                              </td>
                              <td style="text-align: center;"><?php
                                                              $wi_discrete_a = wiDiscrete($a_a, $dm_a, $dpi_a, $delta_dpi_a);
                                                              $wi_discrete_norm_a = $wi_discrete_a / $wi_discrete_sum_a;
                                                              array_push($wi_discrete_norm_a_data, $wi_discrete_a);
                                                              echo number_format($wi_discrete_norm_a, 4, '.', '');
                                                              ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $wi_norm_s = $wi_discrete_a / $wi_discrete_sum_a;

                                $wi_acc_s += $wi_norm_s;
                                echo number_format(round($wi_acc_s, 4), 4, '.', '');

                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $fs_s = fs($dpi_s, $sim_air_free_path);
                                echo number_format(round($fs_s, 2), 2, '.', '');
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $dms_s = dms($dpi_s, $sim_boltzmann, $temperature, $fs_s, $sim_air_viscosity);
                                echo sprintf('%.2E', $dms_s);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $pe_s = pe($diameter, $velocity, $dms_s);
                                echo round($pe_s, 2);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $cd_s = cd($sim_knudsen, $porosity, $pe_s, $sim_kuwabara);
                                echo round($cd_s, 3);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $nd_s = nd($porosity, $sim_kuwabara, $pe_s, $cd_s);
                                array_push($nd_data, $nd_s);
                                echo round($nd_s, 3);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $r_s = r($dpi_s, $diameter);
                                echo round($r_s, 2);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $cr_s = cr($sim_knudsen, $r_s);
                                echo round($cr_s, 3);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $ndi_s = ndi($porosity, $sim_kuwabara, $r_s, $cr_s);
                                array_push($ndi_data, $ndi_s);
                                echo round($ndi_s, 3);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $stk_s = stk($density, $velocity, fs($dpi_a, $sim_air_free_path_a), $dpi_a, $sim_air_viscosity_a, $diameter_a);
                                echo round($stk_s, 2);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $ni_s = ni($stk_s);
                                array_push($ni_data, $ni_s);
                                echo round($ni_s, 3);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $ntotal_s = ntotal($nd_s, $ndi_s, $ni_s);
                                array_push($ntotal_data, $ntotal_s);
                                echo round($ntotal_s, 2);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $egrade_s = egrade($ntotal_s, $sim_density_a, $thickness_a, $diameter_a);
                                array_push($egrade_data, $egrade_s);
                                echo number_format(round($egrade_s, 2), 2);
                                ?>
                              </td>
                              <td style="text-align: center;">
                                <?php
                                $eoveral_s = eoveral($egrade_s, $wi_discrete_norm_a);
                                echo sprintf('%.2E', $eoveral_s);
                                array_push($eoveral_sum, $eoveral_s);
                                ?>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                        <tr class="sum-row">
                          <td colspan="15" style="text-align: right;">SUM</td>
                          <td ><?= number_format(array_sum($eoveral_sum), 9, '.', '') ?></td>
                        </tr>
                      </table>

<?php $html_simulation_table = ob_get_clean();

// Performance parameters (same calls as before, now shown as readouts)
$mpps_value = mpps($dpi_data_a, $egrade_data);
$emin_value = minimum_grade_efficiency($egrade_data);
$overall_filter_efficiency = overall_filter_efficiency(array_sum($eoveral_sum));
$flow_rate_value = flow_rate($velocity_a, $area_a);
$pressure_drop = pressure_drop($sim_air_viscosity_a, $sim_permeability_k1_a, $velocity_a, $sim_density_a, $sim_permeability_k2_a, $thickness_a);
$viscous_contribution = viscous_contribution($sim_air_viscosity_a, $sim_permeability_k1_a, $velocity_a, $thickness_a, $pressure_drop);
$inertial_contribution = inertial_contribution($viscous_contribution);
$quality_factor = quality_factor($overall_filter_efficiency, $pressure_drop);
$outlet_dust_concentration = outlet_dust_concentration($concentration_a, $overall_filter_efficiency);
$fmt = function ($v, $d = 2) { return $v === null ? '&mdash;' : number_format($v, $d, '.', ''); };

/* ---------------------------------------------------------------------------
 * How good is each result *in this simulation*: verdict + reference bands.
 * Bands are indicative (standards / literature), shown in the (i) dialogs.
 * level: good | moderate | poor | neutral
 * ------------------------------------------------------------------------- */
// Pick the band a value falls in; $bands ordered as displayed, each with a test
$band = function ($value, array $bands) {
  $rows = [];
  $hit = null;
  foreach ($bands as $b) {
    $match = $hit === null && $value !== null && $b['test']($value);
    if ($match) $hit = $b;
    $rows[] = ['range' => $b['range'], 'label' => $b['label'], 'level' => $b['level'], 'current' => $match];
  }
  return [$hit, $rows];
};
$n = function ($v, $d = 2) { return $v === null ? '—' : number_format($v, $d, '.', ''); };

$kpi_assessment = [];

// Overall efficiency (higher is better)
[$hit, $rows] = $band($overall_filter_efficiency, [
  ['range' => '≥ 99.97 %', 'label' => 'HEPA level', 'level' => 'good', 'test' => function ($v) { return $v >= 99.97; }],
  ['range' => '99 – 99.97 %', 'label' => 'Excellent', 'level' => 'good', 'test' => function ($v) { return $v >= 99; }],
  ['range' => '95 – 99 %', 'label' => 'Good', 'level' => 'good', 'test' => function ($v) { return $v >= 95; }],
  ['range' => '80 – 95 %', 'label' => 'Moderate', 'level' => 'moderate', 'test' => function ($v) { return $v >= 80; }],
  ['range' => '< 80 %', 'label' => 'Low', 'level' => 'poor', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['efficiency'] = [
  'value' => $n($overall_filter_efficiency) . ' %', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => 'The membrane captures ' . $n($overall_filter_efficiency) . ' % of the incoming particle mass; '
          . $n(100 - $overall_filter_efficiency) . ' % passes through. This is a mass-based figure over the whole size distribution, so it is usually higher than the efficiency at the most penetrating size (E<sub>min</sub>).',
];

// Pressure drop (lower is better)
[$hit, $rows] = $band($pressure_drop, [
  ['range' => '< 50 Pa', 'label' => 'Very low', 'level' => 'good', 'test' => function ($v) { return $v < 50; }],
  ['range' => '50 – 150 Pa', 'label' => 'Low', 'level' => 'good', 'test' => function ($v) { return $v < 150; }],
  ['range' => '150 – 350 Pa', 'label' => 'Moderate', 'level' => 'moderate', 'test' => function ($v) { return $v < 350; }],
  ['range' => '≥ 350 Pa', 'label' => 'High', 'level' => 'poor', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['pressure_drop'] = [
  'value' => $n($pressure_drop) . ' Pa', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => 'At the face velocity of this simulation (' . $n($velocity * 100, 1) . ' cm/s). For comparison, respirator standards limit breathing resistance to roughly 240–350 Pa at their test flow, and HEPA media typically run around 250–300 Pa at about 5 cm/s. Pressure drop grows with face velocity and thickness, so compare membranes at the same conditions.',
];

// Quality factor (higher is better)
[$hit, $rows] = $band($quality_factor, [
  ['range' => '≥ 0.10 Pa⁻¹', 'label' => 'Very high', 'level' => 'good', 'test' => function ($v) { return $v >= 0.10; }],
  ['range' => '0.05 – 0.10 Pa⁻¹', 'label' => 'High', 'level' => 'good', 'test' => function ($v) { return $v >= 0.05; }],
  ['range' => '0.02 – 0.05 Pa⁻¹', 'label' => 'Typical', 'level' => 'moderate', 'test' => function ($v) { return $v >= 0.02; }],
  ['range' => '< 0.02 Pa⁻¹', 'label' => 'Low', 'level' => 'poor', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['quality_factor'] = [
  'value' => $n($quality_factor) . ' Pa⁻¹', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => 'Commercial glass-fibre and melt-blown media are usually around 0.01–0.04 Pa⁻¹; electrospun nanofibre media often reach 0.05 or more. Note that here QF uses the mass-based overall efficiency, which gives higher values than a QF computed from the efficiency at the MPPS, so compare it with QFs calculated the same way.',
];

// MPPS (neither good nor bad by itself: it locates the weak spot)
[$hit, $rows] = $band($mpps_value, [
  ['range' => '< 100 nm', 'label' => 'Below the usual range', 'level' => 'neutral', 'test' => function ($v) { return $v < 100; }],
  ['range' => '100 – 400 nm', 'label' => 'Usual range for fibrous filters', 'level' => 'neutral', 'test' => function ($v) { return $v <= 400; }],
  ['range' => '> 400 nm', 'label' => 'Larger than usual', 'level' => 'moderate', 'test' => function ($v) { return true; }],
]);
$mpps_text = 'The MPPS is not good or bad by itself: it tells you <strong>which</strong> particle size gets through most easily. What decides the quality is the efficiency at that size, E<sub>min</sub>, which here is <strong>' . $n($emin_value) . ' %</strong>. '
           . 'With an MPPS of ' . $n($mpps_value, 0) . ' nm, the particles this membrane stops worst are about ' . $n($mpps_value / 1000, 2) . ' µm across';
$mpps_text .= ($mpps_value >= 100 && $mpps_value <= 2500) ? ', in the fine-particle range (PM<sub>1</sub>–PM<sub>2.5</sub>) that matters most for health.' : '.';
if ($mpps_value > 400) {
  $mpps_text .= ' An MPPS above the usual 100–400 nm happens with coarse fibres or low face velocities.';
  if ($diameter > 1) {
    $mpps_text .= ' This simulation uses a fibre diameter of <strong>' . $n($diameter) . ' µm</strong>, while electrospun fibres are usually 0.1–1 µm: check this input, as finer fibres would lower the MPPS and raise E<sub>min</sub>.';
  }
}
// The minimum can fall on the first/last size class: then the true MPPS may lie
// outside the simulated range and the usual-range verdict would mislead
$mpps_edge = null;
if ($mpps_value !== null) {
  if (abs($mpps_value - $min_particle_size_a * 1000) <= 0.01 * $min_particle_size_a * 1000) $mpps_edge = 'smallest';
  if (abs($mpps_value - $max_particle_size_a * 1000) <= 0.01 * $max_particle_size_a * 1000) $mpps_edge = 'largest';
}
if ($mpps_edge) {
  $hit = ['label' => 'At the edge of the simulated range', 'level' => 'moderate'];
  foreach ($rows as &$r) { $r['current'] = false; }
  unset($r);
  $mpps_text = 'The lowest grade efficiency falls on the <strong>' . $mpps_edge . '</strong> particle size you simulated ('
             . ($mpps_edge === 'smallest' ? $n((float) $min_particle_size_a, 3) : $n((float) $max_particle_size_a, 3)) . ' µm), '
             . 'so the true MPPS may lie outside the range entered. Widen the minimum/maximum particle sizes to locate it.';
  if ($emin_value !== null && $emin_value >= 99.9) {
    $mpps_text .= ' Here the grade efficiency is practically 100 % for every size simulated, so there is no real weak spot inside this range.';
  }
}
$kpi_assessment['mpps'] = [
  'value' => $n($mpps_value, 0) . ' nm', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => $mpps_text,
];

// Minimum grade efficiency (higher is better)
[$hit, $rows] = $band($emin_value, [
  ['range' => '≥ 99.97 %', 'label' => 'HEPA level', 'level' => 'good', 'test' => function ($v) { return $v >= 99.97; }],
  ['range' => '99 – 99.97 %', 'label' => 'FFP3 / N99 level', 'level' => 'good', 'test' => function ($v) { return $v >= 99; }],
  ['range' => '95 – 99 %', 'label' => 'N95 level', 'level' => 'good', 'test' => function ($v) { return $v >= 95; }],
  ['range' => '94 – 95 %', 'label' => 'FFP2 level', 'level' => 'good', 'test' => function ($v) { return $v >= 94; }],
  ['range' => '80 – 94 %', 'label' => 'Moderate', 'level' => 'moderate', 'test' => function ($v) { return $v >= 80; }],
  ['range' => '< 80 %', 'label' => 'Low', 'level' => 'poor', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['emin'] = [
  'value' => $n($emin_value) . ' %', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => 'At its weakest size (' . $n($mpps_value, 0) . ' nm) the membrane captures ' . $n($emin_value) . ' % of the particles, so up to ' . $n(100 - $emin_value) . ' % of them pass. Respirator and HEPA classes are rated at the most penetrating size: N95 ≥ 95 %, FFP2 ≥ 94 %, FFP3 ≥ 99 %, HEPA ≥ 99.97 %. Certification uses specific test aerosols and flows, so treat this as an indication, not a rating.',
];

// Flow rate (operating condition, no good/bad)
[$hit, $rows] = $band($flow_rate_value, [
  ['range' => '< 30 L/min', 'label' => 'Below breathing at rest', 'level' => 'neutral', 'test' => function ($v) { return $v < 30; }],
  ['range' => '30 – 85 L/min', 'label' => 'Between rest and heavy breathing', 'level' => 'neutral', 'test' => function ($v) { return $v < 85; }],
  ['range' => '≥ 85 L/min', 'label' => 'Respirator test flow or above', 'level' => 'neutral', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['flow_rate'] = [
  'value' => $n($flow_rate_value) . ' L/min', 'verdict' => $hit['label'] ?? '—', 'level' => 'neutral', 'bands' => $rows,
  'text' => 'This is an input condition (face velocity × filter area), not a measure of quality. Reference flows: about 30 L/min for breathing at rest, 85 L/min for the NIOSH respirator test and 95 L/min for EN 149. Efficiency and pressure drop both change with flow, so compare membranes at the same flow.',
];

// Outlet concentration (depends on the inlet and on the applicable limit)
$kpi_assessment['outlet'] = [
  'value' => $n($outlet_dust_concentration) . ' mg/m³', 'verdict' => 'Depends on the applicable limit', 'level' => 'neutral', 'bands' => [],
  'text' => $n(100 - $overall_filter_efficiency) . ' % of the inlet concentration you entered (' . $n((float) $concentration) . ' mg/m³) reaches the clean side. There is no universal threshold: compare it with the exposure or emission limit of your application. For scale, the WHO 24-hour guideline for PM<sub>2.5</sub> is 0.015 mg/m³.',
];

// Viscous / inertial split
[$hit, $rows] = $band($viscous_contribution, [
  ['range' => '≥ 90 % viscous', 'label' => 'Laminar (Darcy) regime', 'level' => 'good', 'test' => function ($v) { return $v >= 90; }],
  ['range' => '70 – 90 % viscous', 'label' => 'Mixed regime', 'level' => 'moderate', 'test' => function ($v) { return $v >= 70; }],
  ['range' => '< 70 % viscous', 'label' => 'Inertia-dominated', 'level' => 'poor', 'test' => function ($v) { return true; }],
]);
$kpi_assessment['viscous_inertial'] = [
  'value' => $n($viscous_contribution, 1) . ' % / ' . $n($inertial_contribution, 1) . ' %', 'verdict' => $hit['label'] ?? '—', 'level' => $hit['level'] ?? 'neutral', 'bands' => $rows,
  'text' => $n($viscous_contribution, 1) . ' % of the pressure drop comes from viscous friction. A laminar regime is what is expected in air filtration: the pressure drop then grows roughly in proportion to velocity, so it stays predictable if the flow changes. A large inertial share would make it rise much faster at higher velocities.',
];
$ree_id = (int) $this->getView()->filter_simulation->__get('fk_research_ree_id');
?>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">

    <a href="/dashboard/researcher/research/view/<?= $ree_id; ?>" class="d-inline-flex align-items-center mb-2">
      <i class="bx bx-chevron-left"></i> Back to the study
    </a>
    <span class="empp-eyebrow">Filtration performance simulation</span>
    <h4 class="mb-4"><?= htmlspecialchars($material); ?> membrane</h4>


    <!-- Key results -->
<?php
    // Label row of a result tile, with the button that opens its explanation
    $kpi_label = function ($key, $label) {
      echo '<div class="kpi-label"><span class="empp-eyebrow">' . $label . '</span>'
         . '<button type="button" class="kpi-info" data-kpi="' . $key . '" aria-label="What is ' . strip_tags($label) . '?">'
         . '<i class="bx bx-info-circle"></i></button></div>';
    };
    ?>
    <div class="kpi-grid mb-4">
      <div class="kpi-hero">
        <?php $kpi_label('efficiency', 'Overall efficiency, E<sub>overall</sub>'); ?>
        <div class="empp-readout"><?= $fmt($overall_filter_efficiency); ?><span class="empp-unit">%</span></div>
      </div>
      <div class="kpi-hero">
        <?php $kpi_label('pressure_drop', 'Pressure drop, &Delta;P'); ?>
        <div class="empp-readout"><?= $fmt($pressure_drop); ?><span class="empp-unit">Pa</span></div>
      </div>
      <div class="kpi-hero">
        <?php $kpi_label('quality_factor', 'Quality factor, QF'); ?>
        <div class="empp-readout"><?= $fmt($quality_factor); ?><span class="empp-unit">Pa<sup>-1</sup></span></div>
      </div>
      <div>
        <?php $kpi_label('mpps', 'MPPS'); ?>
        <div class="empp-readout"><?= $fmt($mpps_value); ?><span class="empp-unit">nm</span></div>
        <small>Most penetrating particle size</small>
      </div>
      <div>
        <?php $kpi_label('emin', 'E<sub>min</sub> at MPPS'); ?>
        <div class="empp-readout"><?= $fmt($emin_value); ?><span class="empp-unit">%</span></div>
        <small>Minimum grade efficiency</small>
      </div>
      <div>
        <?php $kpi_label('flow_rate', 'Flow rate, Q'); ?>
        <div class="empp-readout"><?= $fmt($flow_rate_value); ?><span class="empp-unit">L/min</span></div>
      </div>
      <div>
        <?php $kpi_label('outlet', 'Outlet concentration'); ?>
        <div class="empp-readout"><?= $fmt($outlet_dust_concentration); ?><span class="empp-unit">mg/m&sup3;</span></div>
      </div>
      <div>
        <?php $kpi_label('viscous_inertial', 'Viscous / inertial'); ?>
        <div class="empp-readout"><?= $fmt($viscous_contribution, 1); ?><span class="empp-unit">/ <?= $fmt($inertial_contribution, 1); ?> %</span></div>
        <small>Contribution to &Delta;P</small>
      </div>
    </div>

    <script>
    // Explanations for the result tiles (opened by the (i) buttons)
    (function () {
      var info = {
        efficiency: {
          title: 'Overall efficiency, E<sub>overall</sub>',
          what: 'The share of the incoming particle <strong>mass</strong> that the membrane captures, over the whole particle size distribution of the aerosol.',
          read: 'Higher is better. 100% would mean no particle mass passes through.',
          how: 'Grade efficiency of each size class weighted by that class\'s mass fraction: E<sub>overall</sub> = &Sigma; E<sub>grade</sub>(d<sub>pi</sub>) &middot; W<sub>i</sub>.'
        },
        pressure_drop: {
          title: 'Pressure drop, &Delta;P',
          what: 'The resistance the membrane offers to the air flow: the pressure lost between the upstream and downstream faces at the chosen face velocity.',
          read: 'Lower is better. It sets the energy needed to push air through the filter (or the breathing effort, for a mask).',
          how: 'Darcy (viscous) plus Forchheimer (inertial) terms over the thickness: &Delta;P = (&mu;&middot;v<sub>s</sub>/k<sub>1</sub> + &alpha;&middot;v<sub>s</sub>&sup2;/k<sub>2</sub>) &middot; L, where &mu; is the air viscosity and &alpha; the packing density (1 &minus; &epsilon;).'
        },
        quality_factor: {
          title: 'Quality factor, QF',
          what: 'The trade-off between capturing particles and letting air through, in a single number. It is the usual metric for comparing different filter media.',
          read: 'Higher is better: more efficiency for the same pressure drop.',
          how: 'QF = &minus;ln(1 &minus; E<sub>overall</sub>) / &Delta;P, in Pa<sup>&minus;1</sup>.'
        },
        mpps: {
          title: 'Most penetrating particle size (MPPS)',
          what: 'The particle diameter the membrane captures <strong>worst</strong>. Smaller particles are caught by diffusion and larger ones by interception and impaction; at the MPPS all these mechanisms are weak at the same time.',
          read: 'It locates the filter\'s weak spot. Filter standards (e.g. HEPA, respirator classes) test at this size.',
          how: 'The size class where the grade efficiency E<sub>grade</sub> is lowest, shown in nanometres.'
        },
        emin: {
          title: 'Minimum grade efficiency, E<sub>min</sub>',
          what: 'The collection efficiency at the MPPS: the worst-case efficiency of the membrane across all particle sizes.',
          read: 'Higher is better. It is the conservative figure to report, since every other size is captured at least this well.',
          how: 'The lowest value of E<sub>grade</sub> over all size classes, in %.'
        },
        flow_rate: {
          title: 'Flow rate, Q',
          what: 'The volume of air passing through the membrane per minute. It is an operating condition of the simulation, not a result of the membrane\'s quality.',
          read: 'Use it to compare with the flow of a real application (a respirator test, a ventilation duct, etc.).',
          how: 'Face velocity times filter area: Q = v<sub>s</sub> &middot; A, converted to L/min.'
        },
        outlet: {
          title: 'Outlet concentration',
          what: 'The particle mass concentration in the air leaving the filter, for the inlet concentration you entered.',
          read: 'Lower is better. Compare it with the exposure or emission limit that applies to your case.',
          how: 'C<sub>out</sub> = C<sub>in</sub> &middot; (1 &minus; E<sub>overall</sub>), in mg/m&sup3;.'
        },
        viscous_inertial: {
          title: 'Viscous / inertial contribution',
          what: 'How the pressure drop splits between the viscous term (friction of air flowing slowly around the fibres) and the inertial term (losses that grow with the square of the velocity).',
          read: 'A viscous-dominated split means laminar flow: the pressure drop grows roughly in proportion to the air velocity. A large inertial share means it grows faster at higher velocities.',
          how: 'Viscous % = (&mu;&middot;v<sub>s</sub>/k<sub>1</sub>&middot;L) / &Delta;P; inertial % is the remainder.'
        }
      };

      // Verdict for the values of this simulation (computed server-side)
      var assessment = <?= json_encode($kpi_assessment, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE); ?>;

      function esc(s) {
        return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
      }

      // Verdict for this simulation (left column)
      function thisSimulation(a) {
        if (!a) return '';
        return '<div class="kpi-verdict kpi-' + a.level + '">' +
            '<div class="kpi-verdict-head"><span class="kpi-verdict-value">' + esc(a.value) + '</span>' +
            '<span class="kpi-badge">' + esc(a.verdict) + '</span></div>' +
            '<p>' + a.text + '</p></div>';
      }

      // Reference bands with the current one marked (right column)
      function referenceBands(a) {
        if (!a || !a.bands || !a.bands.length) return '';
        return '<h6 class="mt-0">Reference ranges</h6><table class="kpi-bands"><tbody>' + a.bands.map(function (b) {
          return '<tr class="' + (b.current ? 'is-current kpi-' + b.level : '') + '">' +
            '<td>' + esc(b.range) + '</td><td>' + esc(b.label) + (b.current ? ' <strong>&larr; this simulation</strong>' : '') + '</td></tr>';
        }).join('') + '</tbody></table>';
      }
      document.querySelectorAll('.kpi-info').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var key = btn.getAttribute('data-kpi');
          var item = info[key];
          if (!item) return;
          Swal.fire({
            title: item.title,
            html:
              '<div class="kpi-explainer">' +
                '<div class="kpi-col">' +
                  '<p>' + item.what + '</p>' +
                  '<h6>In this simulation</h6>' + thisSimulation(assessment[key]) +
                '</div>' +
                '<div class="kpi-col">' + referenceBands(assessment[key]) +
                  '<h6 class="kpi-read">How to read it</h6><p>' + item.read + '</p>' +
                  '<h6>How EMPP calculates it</h6><p class="kpi-formula">' + item.how + '</p>' +
                  '<p class="kpi-disclaimer">Reference ranges are indicative, taken from filtration standards and literature. A simulation is not a certified test.</p>' +
                '</div>' +
              '</div>',
            confirmButtonText: 'Got it',
            width: '70%',
            customClass: { popup: 'kpi-popup' }
          });
        });
      });
    })();
    </script>

    <!-- Details -->
    <div class="card">
      <div class="card-body pb-0">
        <ul class="nav sim-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-charts" type="button" role="tab">Charts</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-inputs" type="button" role="tab">Inputs &amp; properties</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-distribution" type="button" role="tab">Size distribution</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-simulation" type="button" role="tab">Simulation table</button>
          </li>
        </ul>
      </div>
      <div class="card-body tab-content pt-5">

        <!-- Charts -->
        <div class="tab-pane fade show active" id="tab-charts" role="tabpanel">
          <div class="row g-4">
            <div class="col-xl-6" id="graphic2v">
              <h6 class="mb-1">Grade efficiency and mass frequency</h6>
              <p class="chart-note">Collection efficiency per particle size, over the particle mass distribution.</p>
              <p class="chart-note" id="chart2-wait">Generating chart&hellip;</p>
              <div class="chart-box"><canvas id="graphic2" style="display: none;"></canvas></div>
              <table id="dataTable" style="display:none"><thead><tr><th></th><th></th><th></th></tr></thead><tbody></tbody></table>
            </div>
            <div class="col-xl-6" id="graphic3v">
              <h6 class="mb-1">Single-fiber efficiency by mechanism</h6>
              <p class="chart-note">Diffusion, interception and impaction contributions to the total single-fiber efficiency.</p>
              <div class="chart-box"><canvas id="graphic3"></canvas></div>
            </div>
            <div class="col-12" id="graphic1v">
              <h6 class="mb-1">Particle size distribution</h6>
              <p class="chart-note">Mass frequency (left axis) and cumulative frequency (right axis) against particle diameter, log scale.</p>
              <div class="controls" style="display:none">
                <select id="distributionType"><option value="normal">Normal</option><option value="bimodal">Bimodal</option><option value="skewed">Skewed</option></select>
                <input type="range" id="meanSize" min="1" max="50" value="10" step="1"><span id="meanSizeValue">10 &micro;m</span>
                <input type="range" id="stdDev" min="1" max="20" value="5" step="1"><span id="stdDevValue">5</span>
                <button id="updateChart" type="button">Update chart</button>
              </div>
              <div class="chart-box"><canvas id="particleSizeChart"></canvas></div>
            </div>
          </div>
        </div>

        <!-- Inputs & derived properties -->
        <div class="tab-pane fade" id="tab-inputs" role="tabpanel">
          <div class="row g-5">
            <div class="col-lg-4">
              <span class="empp-eyebrow mb-3">Filter</span>
              <dl class="props-list">
                <dt>Material</dt><dd><?= htmlspecialchars($material) ?></dd>
                <dt>Thickness, L (mm)</dt><dd><?= htmlspecialchars($thickness) ?></dd>
                <dt>Avg. fiber diameter, d<sub>f</sub> (&micro;m)</dt><dd><?= htmlspecialchars($diameter) ?></dd>
                <dt>Porosity, &epsilon; (&ndash;)</dt><dd><?= htmlspecialchars($porosity) ?></dd>
                <dt>Packing density, &alpha; (&ndash;)</dt><dd><?= htmlspecialchars($sim_density) ?></dd>
                <dt>Permeability k<sub>1</sub> (m&sup2;)</dt><dd><?= htmlspecialchars($sim_permeability_k1) ?></dd>
                <dt>Permeability k<sub>2</sub> (m)</dt><dd><?= htmlspecialchars($sim_permeability_k2) ?></dd>
              </dl>
            </div>
            <div class="col-lg-4">
              <span class="empp-eyebrow mb-3">Operation</span>
              <dl class="props-list">
                <dt>Test temperature, T (&deg;C)</dt><dd><?= htmlspecialchars($temperature) ?></dd>
                <dt>Pressure, P (mmHg)</dt><dd><?= htmlspecialchars($pressure) ?></dd>
                <dt>Air face velocity, v<sub>s</sub> (m/s)</dt><dd><?= htmlspecialchars($velocity) ?></dd>
                <dt>Filter area, A (m&sup2;)</dt><dd><?= htmlspecialchars($area) ?></dd>
                <dt>Gravitational acceleration (m/s&sup2;)</dt><dd><?= htmlspecialchars($sim_gravitational) ?></dd>
                <dt>Air density (kg/m&sup3;)</dt><dd><?= round($sim_air_density, 3) ?></dd>
                <dt>Air viscosity (Pa&middot;s)</dt><dd><?= htmlspecialchars($sim_air_viscosity) ?></dd>
                <dt>Air mean free path (m)</dt><dd><?= htmlspecialchars($sim_air_free_path) ?></dd>
                <dt>Test pressure (Pa)</dt><dd><?= round($sim_test_pressure, 0) ?></dd>
              </dl>
            </div>
            <div class="col-lg-4">
              <span class="empp-eyebrow mb-3">Aerosol</span>
              <dl class="props-list">
                <dt>Particle density, &rho;<sub>p</sub> (kg/m&sup3;)</dt><dd><?= htmlspecialchars($density) ?></dd>
                <dt>Min. particle size, d<sub>pi,min</sub> (&micro;m)</dt><dd><?= htmlspecialchars($min_particle_size) ?></dd>
                <dt>Max. particle size, d<sub>pi,max</sub> (&micro;m)</dt><dd><?= htmlspecialchars($max_particle_size) ?></dd>
                <dt>Inlet concentration, C (mg/m&sup3;)</dt><dd><?= htmlspecialchars($concentration) ?></dd>
                <dt>Size classes</dt><dd><?= htmlspecialchars($class) ?></dd>
                <dt>Boltzmann constant, k<sub>B</sub> (J/K)</dt><dd><?= htmlspecialchars($sim_boltzmann) ?></dd>
                <dt>Kuwabara parameter, Ku (&ndash;)</dt><dd><?= round($sim_kuwabara, 4) ?></dd>
                <dt>Fiber Knudsen number, Kn<sub>f</sub> (&ndash;)</dt><dd><?= round($sim_knudsen, 5) ?></dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Size distribution -->
        <div class="tab-pane fade" id="tab-distribution" role="tabpanel">
          <div class="row g-4">
            <div class="col-lg-5">
              <span class="empp-eyebrow mb-2">Summary</span>
              <div class="table-responsive"><?= $html_distribution_summary; ?></div>
            </div>
            <div class="col-lg-7">
              <span class="empp-eyebrow mb-2">Discrete distribution by class</span>
              <div class="table-scroll"><?= $html_distribution_table; ?></div>
            </div>
          </div>
        </div>

        <!-- Full simulation table -->
        <div class="tab-pane fade" id="tab-simulation" role="tabpanel">
          <p class="chart-note">Per-class intermediate values of the single-fiber and filter efficiency model.</p>
          <div class="table-scroll"><?= $html_simulation_table; ?></div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

<script>
    // Theme defaults for every chart on this page
    Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";
    Chart.defaults.color = '#6a7284';
    Chart.defaults.borderColor = '#e1e4ea';
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.elements.line.borderWidth = 2;
    Chart.defaults.elements.point.radius = 0;
    Chart.defaults.elements.point.hoverRadius = 4;
    Chart.defaults.interaction.mode = 'index';
    Chart.defaults.interaction.intersect = false;
    /**
     * @fileoverview This script initializes and manages three different Chart.js graphs
     * on the same page, ensuring all are rendered correctly after the DOM content
     * has been loaded. It uses discrete data passed from a PHP backend.
     */

    // =========================================================================
    // CHART 1: Particle Size Distribution (Mass Frequency & Cumulative Frequency)
    // =========================================================================

    /**
     * Generates data for the particle size distribution chart using discrete data from PHP.
     * It normalizes the mass frequency and calculates the cumulative frequency.
     * @returns {object} An object containing diameters, massFrequency, and cumulativeFrequency arrays.
     */
    function generateDataChart1() {
      // Use discrete data passed from PHP
      const diameters = JSON.parse('<?= str_replace("\"", "", json_encode($dpi_data)) ?>');
      let massFrequency = JSON.parse('<?= str_replace("\"", "", json_encode($wi_discrete_data)) ?>');
      let cumulativeFrequency = [];

      // Normalize mass frequency to ensure the sum is 1 (100%)
      const sum = massFrequency.reduce((a, b) => a + b, 0);
      massFrequency = massFrequency.map(v => v / sum);

      // Calculate cumulative frequency
      let cumulative = 0;
      for (let i = 0; i < massFrequency.length; i++) {
        cumulative += massFrequency[i];
        cumulativeFrequency.push(cumulative);
      }

      return {
        diameters,
        massFrequency,
        cumulativeFrequency
      };
    }

    /**
     * Updates the HTML table with the provided data.
     * @param {Array<number>} diameters - Array of particle diameters.
     * @param {Array<number>} massFrequency - Array of mass frequencies.
     * @param {Array<number>} cumulativeFrequency - Array of cumulative frequencies.
     */
    function updateDataTable(diameters, massFrequency, cumulativeFrequency) {
      const tableBody = document.querySelector('#dataTable tbody');
      if (!tableBody) {
        console.error('Element #dataTable tbody not found!');
        return;
      }
      tableBody.innerHTML = '';
      for (let i = 0; i < diameters.length; i++) {
        const row = document.createElement('tr');
        const diamCell = document.createElement('td');
        diamCell.textContent = diameters[i].toFixed(2);
        const massCell = document.createElement('td');
        massCell.textContent = massFrequency[i].toFixed(4);
        const cumCell = document.createElement('td');
        cumCell.textContent = cumulativeFrequency[i].toFixed(2);
        row.appendChild(diamCell);
        row.appendChild(massCell);
        row.appendChild(cumCell);
        tableBody.appendChild(row);
      }
    }

    /**
     * Initializes and renders the first Chart.js graph.
     */
    function initChart1() {
      const ctx = document.getElementById('particleSizeChart').getContext('2d');
      const data = generateDataChart1();
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.diameters.map(d => d.toFixed(2)),
          datasets: [{
            label: 'Mass Frequency',
            data: data.massFrequency,
            borderColor: '#2a78d6',
            backgroundColor: 'rgba(42, 120, 214, 0.12)',
            yAxisID: 'y',
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointBackgroundColor: '#2a78d6',
          }, {
            label: 'Cumulative Frequency',
            data: data.cumulativeFrequency,
            borderColor: '#eb6834',
            backgroundColor: 'rgba(235, 104, 52, 0)',
            yAxisID: 'y1',
            tension: 0.4,
            pointRadius: 0,
            pointBackgroundColor: '#eb6834',
          }]
        },
        options: {
          responsive: true,
          interaction: {
            mode: 'index',
            intersect: false
          },
          scales: {
            x: {
              type: 'logarithmic',
              position: 'bottom',
              title: {
                display: true,
                text: 'Particle Diameter (μm)',
                font: {
                  weight: 'bold',
                  size: 14
                }
              },
              min: 0.01,
              max: 100,
              ticks: {
                callback: function(value) {
                  // Display specific ticks on the logarithmic scale
                  return (value === 0.01 || value === 0.1 || value === 1 || value === 10 || value === 100) ? value : '';
                }
              },
            },
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              title: {
                display: true,
                text: 'Mass Frequency',
                font: {
                  weight: 'bold',
                  size: 14
                }
              },
              min: 0,
              max: 0.05,
              ticks: {
                stepSize: 0.005,
                callback: value => value.toFixed(3)
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              title: {
                display: true,
                text: 'Cumulative Frequency',
                font: {
                  weight: 'bold',
                  size: 14
                }
              },
              min: 0,
              max: 1,
              ticks: {
                stepSize: 0.1,
                callback: value => value.toFixed(2)
              },
              grid: {
                drawOnChartArea: false
              }
            }
          },
          plugins: {
            legend: {
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) label += ': ';
                  if (context.datasetIndex === 0) label += context.parsed.y.toFixed(4);
                  else label += context.parsed.y.toFixed(2);
                  return label;
                }
              }
            }
          }
        }
      });
      updateDataTable(data.diameters, data.massFrequency, data.cumulativeFrequency);
    }

    // =========================================================================
    // CHART 2: Grade Efficiency and Mass Frequency
    // =========================================================================

    /**
     * Initializes and renders the second Chart.js graph.
     */
    function createChart2() {
      const dpi_data_a = JSON.parse('<?= str_replace("\"", "", json_encode($dpi_data_a)) ?>');
      const particle_data_a = JSON.parse('<?= str_replace("\"", "", json_encode($wi_discrete_norm_data_a)) ?>');
      const efficiency_data = JSON.parse('<?= str_replace("\"", "", json_encode($egrade_data)) ?>');

      const combined_particle_data = dpi_data_a.map((x, i) => ({
        x: x,
        y: particle_data_a[i]
      }));
      const combined_efficiency_data = dpi_data_a.map((x, i) => ({
        x: x,
        y: efficiency_data[i]
      }));

      // Hide countdown message and show the canvas
      document.getElementById('chart2-wait').style.display = 'none';
      document.getElementById('graphic2').style.display = 'block';

      const data = {
        datasets: [{
          label: 'Grade Efficiency, E_grade (-)',
          data: combined_efficiency_data,
          borderColor: '#1baf7a',
          backgroundColor: 'rgba(27, 175, 122, 0.5)',
          yAxisID: 'y_efficiency',
          tension: 0.1,
          pointRadius: 0
        }, {
          label: 'Mass frequency (-)',
          data: combined_particle_data,
          borderColor: '#2a78d6',
          backgroundColor: 'rgba(42, 120, 214, 0.5)',
          yAxisID: 'y_frequency',
          tension: 0.1,
          pointRadius: 0
        }]
      };

      const config = {
        type: 'line',
        data: data,
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Grade Efficiency Vs. Mass Frequency'
            }
          },
          scales: {
            x: {
              type: 'logarithmic',
              title: {
                display: true,
                text: 'Particle diameter, d_pi (μm)'
              },
              ticks: {
                callback: function(value) {
                  return (value === 0.01 || value === 0.1 || value === 1 || value === 10 || value === 100 || value === 1000) ? value : '';
                }
              }
            },
            y_efficiency: {
              type: 'logarithmic',
              position: 'left',
              title: {
                display: true,
                text: 'Grade Efficiency, E_grade (-)'
              },
              ticks: {
                callback: function(value) {
                  return (value === 0.00001 || value === 0.0001 || value === 0.001 || value === 0.01 || value === 0.1 || value === 1) ? value : '';
                }
              },
              min: 0.00001,
              max: 1
            },
            y_frequency: {
              type: 'linear',
              position: 'right',
              title: {
                display: true,
                text: 'Mass frequency (-)'
              },
              grid: {
                drawOnChartArea: false
              },
              min: 0,
              max: 0.1
            }
          }
        }
      };
      new Chart(document.getElementById('graphic2'), config);
    }

    // =========================================================================
    // CHART 3: Single Fiber Efficiency
    // =========================================================================

    /**
     * Initializes and renders the third Chart.js graph.
     */
    function createGraphic3() {
      // Data passed from PHP (renamed to avoid variable scope conflicts)
      const dpi_data_c = JSON.parse('<?= str_replace("\"", "", json_encode($dpi_data)) ?>');
      const ntotal_data_c = JSON.parse('<?= str_replace("\"", "", json_encode($ntotal_data)) ?>');
      const ndi_data_c = JSON.parse('<?= str_replace("\"", "", json_encode($ndi_data)) ?>');
      const nd_data_c = JSON.parse('<?= str_replace("\"", "", json_encode($nd_data)) ?>');
      const ni_data_c = JSON.parse('<?= str_replace("\"", "", json_encode($ni_data)) ?>');

      const ctx = document.getElementById('graphic3').getContext('2d');

      // Combine diameter data with each efficiency data series
      const combined_ntotal_data = dpi_data_c.map((x, i) => ({
        x: x,
        y: ntotal_data_c[i]
      }));
      const combined_ndi_data = dpi_data_c.map((x, i) => ({
        x: x,
        y: ndi_data_c[i]
      }));
      const combined_nd_data = dpi_data_c.map((x, i) => ({
        x: x,
        y: nd_data_c[i]
      }));
      const combined_ni_data = dpi_data_c.map((x, i) => ({
        x: x,
        y: ni_data_c[i]
      }));

      const data = {
        datasets: [{
          label: 'ηTotal (-)',
          data: combined_ntotal_data,
          borderColor: '#2a78d6', // Red line
          borderWidth: 2,
          fill: false,
          tension: 0.4,
          pointRadius: 0,
        }, {
          label: 'ηDI (-)',
          data: combined_ndi_data,
          borderColor: '#eb6834', // Blue line
          borderWidth: 2,
          borderDash: [2, 10], // Dotted line
          fill: false,
          tension: 0.4,
          pointRadius: 0,
        }, {
          label: 'ηD (-)',
          data: combined_nd_data,
          borderColor: '#1baf7a', // Black line
          borderWidth: 2,
          borderDash: [5, 5], // Dashed line
          fill: false,
          tension: 0.4,
          pointRadius: 0,
        }, {
          label: 'ηI (-)',
          data: combined_ni_data,
          borderColor: '#eda100', // Green line
          borderWidth: 2,
          borderDash: [10, 5], // Dashed line
          fill: false,
          tension: 0.4,
          pointRadius: 0,
        }]
      };
      const config = {
        type: 'line',
        data: data,
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Single fiber efficiency, hT (-) Vs. Particle diameter, dpi (μm)'
            },
            legend: {
              position: 'top'
            }
          },
          scales: {
            x: {
              type: 'logarithmic',
              title: {
                display: true,
                text: 'Particle diameter, dpi (μm)'
              },
              min: 0.01,
              max: 100,
              grid: {
                color: '#e1e4ea'
              },
              ticks: {
                callback: function(value) {
                  return ([0.01, 0.1, 1, 10, 100].includes(value)) ? value : null;
                }
              }
            },
            y: {
              type: 'logarithmic',
              title: {
                display: true,
                text: 'Single fiber efficiency, ηTotal (-)'
              },
              min: 0.00001,
              max: 1,
              grid: {
                color: '#e1e4ea'
              },
              ticks: {
                callback: function(value) {
                  return ([0.00001, 0.0001, 0.001, 0.01, 0.1, 1].includes(value)) ? value : null;
                }
              }
            }
          }
        }
      };
      new Chart(ctx, config);
    }

    /**
     * Main function to initialize all charts after the DOM is fully loaded.
     * This prevents scripts from running on elements that don't exist yet.
     */
    document.addEventListener('DOMContentLoaded', function() {
      initChart1();
      createChart2();
      createGraphic3();
    });


    function toggleVisibility(divId) {
      const myDiv = document.getElementById(divId);

      if (myDiv.style.display === 'none') {
        // If the div is hidden, show it
        myDiv.style.display = 'block'; 
      } else {
        // If the div is visible, hide it
        myDiv.style.display = 'none';
      }
    }


    
  </script>