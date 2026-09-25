<?php
/**
 * The sample study shown on the public site.
 *
 * Process parameters, Haralick features and porosity are illustrative (the
 * porosity model runs on the external API, not here). The filtration figures
 * are computed from them with the same equations as
 * AdministratorController::deriveSimulationProperties() and
 * App/View/administrator/filter_simulation_view.php; keep them in step if
 * those change.
 */

$sample = [
    'name' => 'PCL 12% — sample A',
    'flow' => 0.03,          // mL/min
    'voltage' => 20,         // kV
    'distance' => 15,        // cm
    'features' => [
        'Dissimilarity' => 5.1842,
        'Correlation' => 0.8127,
        'Energy' => 0.0412,
        'Homogeneity' => 0.3968,
    ],
    'porosity' => 84.60,     // % (model output in a real study)
];

// Simulation inputs; porosity comes from the predicted value above
$sim_in = [
    'porosity' => $sample['porosity'] / 100,
    'thickness' => 0.005,    // mm
    'diameter' => 0.5,       // µm, average fibre diameter
    'density' => 1145,       // kg/m³, fibre density (PCL)
    'area' => 0.01,          // m²
    'temperature' => 25,     // °C
    'pressure' => 760,       // mmHg
    'velocity' => 0.05,      // m/s, face velocity
    'size_min' => 0.01,      // µm
    'size_max' => 10,        // µm
    'classes' => 100,
];

$empp_sample_sim = (function ($in) {
    $eps = $in['porosity'];
    $df = $in['diameter'];
    $T = $in['temperature'];
    $v = $in['velocity'];

    // Membrane and air properties (deriveSimulationProperties)
    $alpha = 1 - $eps;
    $k1 = pow($df / 1e6, 2) / (64 * pow($alpha, 1.5) * (1 + 56 * pow($alpha, 3)));
    $k2 = exp(-1.71588 * pow($k1, -0.08093));
    $p_test = ($in['pressure'] / 760) * 101325;
    $mu = 1.73e-5 * pow(($T + 273) / 273, 1.5) * (398 / ($T + 398));
    $lambda = (21.2255 * $mu * pow(273 + $T, 0.5)) / $p_test;
    $kb = 1.380649e-23;
    $ku = (-log($alpha) / 2) - 0.75 + $alpha - pow($alpha, 2) / 4;
    $kn = (2 * $lambda) / ($df / 1e6);

    // Particle size distribution (Rosin-Rammler over log-spaced classes)
    $n = $in['classes'];
    $dmin = $in['size_min'];
    $dmax = $in['size_max'];
    $a = log(1e8 / 1.00000001) / log($dmax / $dmin);
    $dm = $dmax / pow(log(1 / 1e-8), 1 / $a);
    $delta = ($dmax - $dmin) / $n;

    $dpi = [];
    $wi = [];
    for ($x = 0; $x <= $n; $x++) {
        $d = exp(log($dmin) + $x * ((log($dmax) - log($dmin)) / $n));
        $dpi[] = $d;
        $wi[] = $a / $dm * pow($d / $dm, $a - 1) * exp(-pow($d / $dm, $a)) * $delta;
    }
    $wsum = array_sum($wi);

    // Single-fibre efficiency by mechanism, then grade efficiency per class
    $curve = [];
    $overall = 0;
    foreach ($dpi as $i => $d) {
        $fs = 1 + ($lambda / ($d / 1e6)) * (2.33 + 0.966 * exp(-0.4985 * ($d / 1e6) / $lambda));
        $dms = $kb * ($T + 273) * ($fs / (3 * M_PI * $mu * ($d / 1e6)));
        $pe = ($df / 1e6) * $v / $dms;
        $cd = 1 + 0.388 * $kn * pow($eps * $pe / $ku, 1 / 3);
        $nd = 1.6 * pow($eps / $ku, 1 / 3) * pow($pe, -2 / 3) * $cd;
        $r = $d / $df;
        $cr = 1 + 1.996 * $kn / $r;
        $ndi = min(1, 0.6 * ($eps / $ku) * pow($r, 2) / (1 + $r) * $cr);
        $stk = ($in['density'] * $v * $fs * pow($d / 1e6, 2)) / (9 * $mu * ($df / 1e6));
        $ni = pow($stk, 3) / (pow($stk, 3) + 0.77 * pow($stk, 2) + 0.22);
        $nt = 1 - (1 - $nd) * (1 - $ndi) * (1 - $ni);
        $eg = 1 - exp(-4 * $nt * $alpha / (1 - $alpha) * ($in['thickness'] / 1000) / ($df / 1e6));
        $curve[] = [$d, $eg];
        $overall += $eg * ($wi[$i] / $wsum);
    }

    $emin_i = 0;
    foreach ($curve as $i => $p) {
        if ($p[1] < $curve[$emin_i][1]) $emin_i = $i;
    }

    $overall_pct = $overall * 100;
    $dp = ($mu / $k1 * $v + $alpha / $k2 * pow($v, 2)) * ($in['thickness'] / 1000);

    return [
        'curve' => $curve,
        'overall' => $overall_pct,
        'pressure_drop' => $dp,
        'quality_factor' => $overall_pct < 100 ? -log(1 - $overall_pct / 100) / $dp : null,
        'mpps' => $curve[$emin_i][0] * 1000,  // nm
        'emin' => $curve[$emin_i][1] * 100,
        'flow_rate' => $v * $in['area'] * 1000 * 60,  // L/min
    ];
})($sim_in);

return ['sample' => $sample, 'inputs' => $sim_in, 'sim' => $empp_sample_sim];
