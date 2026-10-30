<?php
$v = $this->getView();
$s = $v->study['sample'];
$in = $v->study['inputs'];
$sim = $v->study['sim'];
$res = $v->research;
$logged = !empty($v->logged);
$e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
$num = function ($x, $d = 2) { return number_format((float) $x, $d, '.', ''); };

// Grade-efficiency curve, drawn server-side: log particle size on x, efficiency 90-100 % on y
$plot = ['w' => 640, 'h' => 236, 'l' => 64, 'r' => 12, 't' => 14, 'b' => 44, 'ymin' => 90, 'ymax' => 100];
$px = function ($d) use ($plot, $in) {
    $f = (log10($d) - log10($in['size_min'])) / (log10($in['size_max']) - log10($in['size_min']));
    return $plot['l'] + $f * ($plot['w'] - $plot['l'] - $plot['r']);
};
$py = function ($eff) use ($plot) {
    $pct = max($plot['ymin'], min($plot['ymax'], $eff * 100));
    $f = ($pct - $plot['ymin']) / ($plot['ymax'] - $plot['ymin']);
    return $plot['t'] + (1 - $f) * ($plot['h'] - $plot['t'] - $plot['b']);
};
$path = '';
foreach ($sim['curve'] as $i => $p) {
    $path .= ($i ? 'L' : 'M') . round($px($p[0]), 1) . ' ' . round($py($p[1]), 1);
}
$mpps_x = round($px($sim['mpps'] / 1000), 1);
$mpps_y = round($py($sim['emin'] / 100), 1);

// Research credits from System settings (administrator)
$researchers = array_values(array_filter($res['people'], function ($p) { return $p['per_role'] === 'researcher'; }));
$supervisors = array_values(array_filter($res['people'], function ($p) { return $p['per_role'] === 'supervisor'; }));
$names = function ($list) {
    $n = array_map(function ($p) { return $p['per_name']; }, $list);
    return count($n) > 1 ? implode(', ', array_slice($n, 0, -1)) . ' and ' . end($n) : implode('', $n);
};
$footer_name = $researchers[0]['per_name'] ?? 'EMPP';
$logos = array_values(array_filter(array_merge($res['institutions'], $res['funders']), function ($x) { return !empty($x['ins_logo'] ?? $x['fun_logo'] ?? null); }));
?>
<?php
$page = 'home';
$page_title = $v->title_page;
$page_desc = 'EMPP turns an SEM image of an electrospun membrane and its process parameters into texture features, a predicted porosity and a filtration performance simulation. Free for researchers.';
$extra_head = <<<'HTML'
  <script>
    // Before first paint: hold the record's animated parts at their start state,
    // so the study run never erases values the visitor has already seen.
    // If site.js does not take over, the hold is dropped and everything shows.
    (function (d) {
      if (!window.matchMedia || matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
      d.documentElement.classList.add('js-run');
      setTimeout(function () { if (!window.emppSiteReady) d.documentElement.classList.remove('js-run'); }, 4000);
    })(document);
  </script>
HTML;
include __DIR__ . '/partials/top.php';
?>

    <div class="site-hero site-wrap">
      <h1>From an SEM image to filtration performance.</h1>
      <p class="site-lead">Upload a micrograph of your electrospun membrane with its process parameters. EMPP extracts texture features, predicts porosity and simulates how the membrane filters. Free for researchers.</p>
      <div class="site-hero-actions">
        <?php if ($logged) { ?>
          <a class="site-btn site-btn-light" href="<?= $e($v->dashboard_url); ?>">Open dashboard</a>
        <?php } else { ?>
          <a class="site-btn site-btn-light" href="/sign-up">Create free account</a>
          <a class="site-btn site-btn-ghost" href="/sign-in">Sign in</a>
        <?php } ?>
      </div>
    </div>
  </header>

  <main id="main">
    <!-- The record a study produces, for one sample membrane -->
    <section class="site-wrap rec-wrap" aria-labelledby="rec-title">
      <article class="rec" id="rec">
        <header class="rec-head">
          <h2 class="rec-title" id="rec-title"><?= $e($s['name']); ?></h2>
          <p class="rec-flag">Sample study record · illustrative values</p>
        </header>

        <div class="rec-body">
          <figure class="rec-image">
            <div class="rec-stage">
              <canvas id="micrograph" aria-hidden="true"></canvas>
              <span class="rec-scan" aria-hidden="true"></span>
              <?php // Scale matches the drawn fibres at the sample's d_f: the field is about 63 µm wide ?>
              <div class="rec-bar" aria-hidden="true"><span class="rec-bar-line"></span>10 µm</div>
            </div>
            <figcaption>Example of a generated micrograph; use an SEM image for your study.</figcaption>
          </figure>

          <div class="rec-data">
            <section class="rec-block" data-step="1" aria-labelledby="b1">
              <h3 id="b1"><span class="rec-no" aria-hidden="true">1</span>Process parameters</h3>
              <dl class="rec-values">
                <div><dt>Solution flow rate</dt><dd><?= $num($s['flow']); ?><span>mL/min</span></dd></div>
                <div><dt>Applied voltage</dt><dd><?= $num($s['voltage'], 0); ?><span>kV</span></dd></div>
                <div><dt>Needle–collector distance</dt><dd><?= $num($s['distance'], 0); ?><span>cm</span></dd></div>
              </dl>
            </section>

            <section class="rec-block" data-step="2" aria-labelledby="b2">
              <h3 id="b2"><span class="rec-no" aria-hidden="true">2</span>Haralick texture features </h3>
              <dl class="rec-values rec-values-grid">
                <?php foreach ($s['features'] as $label => $value) { ?>
                  <div><dt><?= $e($label); ?></dt><dd><?= $num($value, 4); ?></dd></div>
                <?php } ?>
              </dl>
            </section>

            <section class="rec-block rec-porosity" data-step="3" aria-labelledby="b3">
              <h3 id="b3"><span class="rec-no" aria-hidden="true">3</span>Predicted porosity</h3>
              <p class="rec-readout"><?= $num($s['porosity']); ?><span>%</span></p>
              <div class="rec-scale" role="img" aria-label="Porosity <?= $num($s['porosity'], 1); ?> percent on a 0 to 100 percent scale">
                <span style="--fill: <?= $num($s['porosity'] / 100, 4); ?>"></span>
              </div>
              <div class="rec-ticks" aria-hidden="true"><span>0</span><span>50</span><span>100 %</span></div>
            </section>
          </div>

          <aside class="rec-notes" aria-label="What each block is">
            <ol>
              <li><span aria-hidden="true">1</span>You upload the SEM micrograph and the three electrospinning parameters the membrane was spun with.</li>
              <li><span aria-hidden="true">2</span>EMPP reads the image's texture: four Haralick statistics of its grey-level co-occurrence matrix.</li>
              <li><span aria-hidden="true">3</span>A machine-learning model predicts porosity from those features and the process parameters.</li>
              <li><span aria-hidden="true">4</span>Single-fibre filtration theory turns the porosity and your membrane data into filtration performance.</li>
            </ol>
          </aside>
        </div>

        <section class="rec-block rec-filtration" data-step="4" aria-labelledby="b4">
          <div class="rec-filt-head">
            <h3 id="b4"><span class="rec-no" aria-hidden="true">4</span>Filtration performance</h3>
            <p>Computed on this page with EMPP's own equations, from the porosity above and
              <span class="nowrap">d<sub>f</sub> <?= $num($in['diameter']); ?> µm</span> · <span class="nowrap">L <?= $num($in['thickness'] * 1000, 0); ?> µm</span> · <span class="nowrap">v <?= $num($in['velocity'] * 100, 0); ?> cm/s</span> · <span class="nowrap"><?= $num($in['temperature'], 0); ?> °C</span>.</p>
          </div>

          <dl class="rec-kpis">
            <div><dt>Overall efficiency</dt><dd><?= $num($sim['overall']); ?><span>%</span></dd></div>
            <div><dt>E<sub>min</sub> at MPPS</dt><dd><?= $num($sim['emin']); ?><span>%</span></dd></div>
            <div><dt>MPPS</dt><dd><?= $num($sim['mpps'], 0); ?><span>nm</span></dd></div>
            <div><dt>Pressure drop</dt><dd><?= $num($sim['pressure_drop'], 1); ?><span>Pa</span></dd></div>
            <div><dt>Quality factor</dt><dd><?= $num($sim['quality_factor'], 3); ?><span>Pa⁻¹</span></dd></div>
          </dl>

          <figure class="rec-curve">
            <svg viewBox="0 0 <?= $plot['w']; ?> <?= $plot['h']; ?>" role="img" aria-labelledby="curve-title">
              <title id="curve-title">Grade efficiency against particle size. Lowest point: <?= $num($sim['emin']); ?> percent at <?= $num($sim['mpps'], 0); ?> nanometres.</title>
              <?php foreach ([90, 95, 100] as $tick) { $y = round($py($tick / 100), 1); ?>
                <line class="grid" x1="<?= $plot['l']; ?>" x2="<?= $plot['w'] - $plot['r']; ?>" y1="<?= $y; ?>" y2="<?= $y; ?>"/>
                <text class="axis" x="<?= $plot['l'] - 8; ?>" y="<?= $y + 4; ?>" text-anchor="end"><?= $tick; ?>%</text>
              <?php } ?>
              <?php foreach ([0.01, 0.1, 1, 10] as $d) { $x = round($px($d), 1);
                // End labels anchor inward so they never run past the plot
                $anchor = $d == 0.01 ? 'start' : ($d == 10 ? 'end' : 'middle'); ?>
                <text class="axis" x="<?= $x; ?>" y="<?= $plot['h'] - 8; ?>" text-anchor="<?= $anchor; ?>"><?= $d < 1 ? $d : (int) $d; ?> µm</text>
              <?php } ?>
              <line class="mpps" x1="<?= $mpps_x; ?>" x2="<?= $mpps_x; ?>" y1="<?= $plot['t']; ?>" y2="<?= $plot['h'] - $plot['b']; ?>"/>
              <path class="curve" d="<?= $path; ?>" pathLength="1"/>
              <circle class="mpps-dot" cx="<?= $mpps_x; ?>" cy="<?= $mpps_y; ?>" r="4.5"/>
              <text class="mpps-label" x="<?= $mpps_x + 10; ?>" y="<?= $mpps_y + 24; ?>">MPPS <?= $num($sim['mpps'], 0); ?> nm · <?= $num($sim['emin']); ?>%</text>
            </svg>
            <figcaption>Grade efficiency by particle size. The dip is the size this membrane stops worst.</figcaption>
          </figure>
        </section>

        <footer class="rec-foot">
          <p>The Haralick texture parameters for your image are populated automatically.</p>
          <a class="site-btn site-btn-primary" href="<?= $logged ? $e($v->dashboard_url) : '/sign-up'; ?>"><?= $logged ? 'Open dashboard' : 'Run your first study'; ?></a>
        </footer>
      </article>
    </section>

    <!-- How a study runs -->
    <section class="site-section site-wrap" id="method" aria-labelledby="method-title">
      <div class="site-section-head">
        <h2 id="method-title">Four steps, one session</h2>
        <p>Each step feeds the next. The first three run for every study; the filtration simulation is optional and can be repeated with other membrane data.</p>
      </div>
      <div class="site-table" role="table" aria-label="Study steps">
        <div class="site-tr site-th" role="row">
          <span role="columnheader">Step</span>
          <span role="columnheader">You provide</span>
          <span role="columnheader">EMPP returns</span>
        </div>
        <div class="site-tr" role="row">
          <span role="cell"><b>1</b>SEM image</span>
          <span role="cell">A micrograph of the membrane surface (JPG, PNG or WEBP, up to 5 MB), cropped in the browser to the fibers</span>
          <span role="cell">The image stored with your research</span>
        </div>
        <div class="site-tr" role="row">
          <span role="cell"><b>2</b>Texture features</span>
          <span role="cell">Nothing more: one click runs the analysis</span>
          <span role="cell">Dissimilarity, correlation, energy, and homogeneity</span>
        </div>
        <div class="site-tr" role="row">
          <span role="cell"><b>3</b>Porosity</span>
          <span role="cell">Solution flow rate, applied voltage and needle–collector distance</span>
          <span role="cell">Predicted membrane porosity</span>
        </div>
        <div class="site-tr" role="row">
          <span role="cell"><b>4</b>Filtration simulation</span>
          <span role="cell">Membrane thickness, average fiber diameter, particle density, filter area, air conditions, particle size range, and inlet concentration</span>
          <span role="cell">Grade and overall filtration efficiency, pressure drop, quality factor, and MPPS</span>
        </div>
      </div>
    </section>

    <!-- Reading the simulation -->
    <section class="site-section site-wrap site-split" id="simulation" aria-labelledby="sim-title">
      <div class="site-section-head">
        <h2 id="sim-title">Results you can read against the standards</h2>
        <p>Every figure in a simulation comes with what it means: respirator and HEPA classes rated at the most penetrating particle size, typical breathing resistance, and the WHO 24-hour guideline for PM<sub>2.5</sub>. Treat them as an indication, not a certification.</p>
      </div>
      <figure class="site-ruler">
        <?php
        $marks = ['FFP2' => 94, 'N95' => 95, 'FFP3' => 99, 'HEPA' => 99.97];
        $pos = function ($pct) { return max(0, min(100, ($pct - 90) / 10 * 100)); };
        $cleared = array_keys(array_filter($marks, function ($t) use ($sim) { return $sim['emin'] >= $t; }));
        $missed = array_keys(array_filter($marks, function ($t) use ($sim) { return $sim['emin'] < $t; }));
        $verdict = ($cleared ? 'The sample membrane clears ' . implode(' and ', $cleared) : 'The sample membrane clears none of these classes')
                 . ($missed ? ' and falls short of ' . implode(' and ', $missed) . '.' : '.');
        ?>
        <div class="ruler-track" role="img" aria-label="Minimum efficiency scale from 90 to 100 percent. Sample membrane: <?= $num($sim['emin']); ?> percent. <?= $e($verdict); ?>">
          <?php $row = 0; foreach ($marks as $name => $pct) { ?>
            <span class="ruler-mark<?= $pos($pct) > 90 ? ' is-end' : ''; ?>" style="--at: <?= $pos($pct); ?>%; --row: <?= $row++ % 2; ?>"><b><?= $name; ?></b><?= $pct; ?>%</span>
          <?php } ?>
          <span class="ruler-sample" style="--at: <?= $pos($sim['emin']); ?>%"><b>Sample</b><?= $num($sim['emin']); ?>%</span>
        </div>
        <div class="ruler-ends" aria-hidden="true"><span>90%</span><span>100%</span></div>
        <figcaption>Efficiency at the most penetrating particle size. <?= $e($verdict); ?></figcaption>
      </figure>
    </section>

    <!-- The research: a summary; the full page is /research -->
    <section class="site-section site-wrap site-research" id="research" aria-labelledby="research-title">
      <div class="site-section-head">
        <h2 id="research-title">The research</h2>
        <p>
          EMPP comes from the research of <?= $e($researchers ? $names($researchers) : $footer_name); ?><?= $supervisors ? ', supervised by ' . $e($names($supervisors)) : ''; ?>.
          It joins image texture analysis, a trained porosity model and classical filtration theory in one tool, and it is free to use.
        </p>
        <p>Your studies are private to your account; the platform's administrators can see them.</p>
      </div>
      <?php if ($logos) { ?>
        <ul class="site-logos" aria-label="Institutions and funding">
          <?php foreach ($logos as $x) {
            $name = $x['ins_name'] ?? $x['fun_name'];
            $file = $x['ins_logo'] ?? $x['fun_logo']; ?>
            <li><img src="<?= $e($_ENV['BASE_IMG'] . 'site/' . rawurlencode($file)); ?>" alt="<?= $e($name); ?>" loading="lazy"></li>
          <?php } ?>
        </ul>
      <?php } ?>
      <a class="site-more" href="/research">About the research: team, publications and funding <span aria-hidden="true">→</span></a>
    </section>

<?php
$scripts = [$_ENV['BASE_JS'] . 'site.js'];
include __DIR__ . '/partials/bottom.php';
