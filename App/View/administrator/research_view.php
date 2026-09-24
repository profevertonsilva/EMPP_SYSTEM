<?php
$research = $this->getView()->research;
$results = $this->getView()->research_results;
$simulation = $this->getView()->filter_simulation;
$is_owner = !empty($this->getView()->is_owner);

$has_features = (bool) $results;
$porosity = $has_features ? $results->__get('rre_porosity') : null;
$has_porosity = $porosity !== null && $porosity !== '';

$haralick = $has_features ? [
  'Dissimilarity' => $results->__get('rre_dissimilarity'),
  'Correlation'   => $results->__get('rre_correlation'),
  'Energy'        => $results->__get('rre_energy'),
  'Homogeneity'   => $results->__get('rre_homogeneity'),
] : [];

// Values written into <script> go through json_encode with every HTML-sensitive
// character escaped, so a stored value can never close the string or the tag.
$empp_js_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$empp_image_url = $_ENV['BASE_IMG'] . 'research/' . rawurlencode(basename((string) $research->__get('ree_file')));
?>
<style>
  .study-image {
    position: relative;
    background: #0e1320;
    border-radius: var(--empp-radius);
    overflow: hidden;
    aspect-ratio: 4 / 3;
  }
  .study-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .study-image figcaption {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 0.5rem 0.75rem;
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    letter-spacing: 0.04em;
    color: #c9cfdf;
    background: linear-gradient(180deg, rgba(14, 19, 32, 0), rgba(14, 19, 32, 0.85));
  }

  .readout-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius);
    overflow: hidden;
  }
  .readout-grid > div {
    padding: 0.875rem 1rem;
    border-right: 1px solid var(--empp-line);
    border-bottom: 1px solid var(--empp-line);
    margin: 0 -1px -1px 0;
  }
  .readout-grid .empp-readout {
    font-size: 1.5rem;
    margin-top: 0.25rem;
  }

  /* Analysis pipeline */
  .pipeline {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .pipeline-step {
    position: relative;
    display: grid;
    grid-template-columns: 2rem 1fr;
    gap: 0 1rem;
    padding-bottom: 1.75rem;
  }
  .pipeline-step:last-child {
    padding-bottom: 0;
  }
  .pipeline-step:not(:last-child)::before {
    content: '';
    position: absolute;
    left: calc(1rem - 1px);
    top: 2rem;
    bottom: 0.25rem;
    width: 2px;
    background: var(--empp-line);
  }
  .pipeline-step.is-done:not(:last-child)::before {
    background: var(--empp-primary);
  }
  .pipeline-marker {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-family: var(--empp-mono);
    font-size: 0.75rem;
    font-weight: 500;
    border: 2px solid var(--empp-line-strong);
    color: var(--empp-muted);
    background: var(--empp-surface);
  }
  .pipeline-step.is-done .pipeline-marker {
    border-color: var(--empp-primary);
    background: var(--empp-primary);
    color: #fff;
  }
  .pipeline-step.is-next .pipeline-marker {
    border-color: var(--empp-primary);
    color: var(--empp-primary);
  }
  .pipeline-title {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 2rem;
    padding-top: 0.25rem;
  }
  .pipeline-title h6 {
    margin: 0;
    font-size: 0.9375rem;
  }
  .pipeline-status {
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--empp-muted);
    white-space: nowrap;
  }
  .pipeline-step.is-done .pipeline-status {
    color: var(--empp-success);
  }
  .pipeline-body {
    grid-column: 2;
    margin-top: 0.5rem;
  }
  .pipeline-body p {
    color: var(--empp-muted);
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
  }

  .haralick-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem 1.5rem;
  }
  .haralick-grid dt {
    font-size: 0.8125rem;
    font-weight: 400;
    color: var(--empp-muted);
  }
  .haralick-grid dd {
    margin: 0;
    font-family: var(--empp-mono);
    font-variant-numeric: tabular-nums;
    font-size: 1.0625rem;
    color: var(--empp-ink);
  }

  /* Predicted porosity: the study's key result */
  .porosity-hero {
    position: relative;
    margin-top: 0.25rem;
    padding: 1.25rem 1.5rem 1.125rem;
    border-radius: var(--empp-radius);
    border: 1px solid rgba(var(--empp-primary-rgb), 0.22);
    border-left: 4px solid var(--empp-primary);
    background: var(--empp-primary-soft);
  }
  .porosity-hero .empp-eyebrow {
    color: var(--empp-primary);
  }
  .porosity-readout {
    font-size: 4rem;
    font-weight: 600;
    line-height: 1;
    margin-top: 0.375rem;
    color: var(--empp-primary);
    letter-spacing: -0.03em;
  }
  .porosity-readout .empp-unit {
    color: inherit;
    opacity: 0.7;
  }
  /* The key result blinks between dark and light red */
  #predicted-porosity {
    color: #c81e1e;
  }
  @media (prefers-reduced-motion: no-preference) {
    #predicted-porosity {
      animation: porosity-blink 1.5s ease-in-out infinite;
    }
  }
  @keyframes porosity-blink {
    0%, 100% { color: #9b1515; }
    50%      { color: #f05252; }
  }
  .porosity-scale {
    position: relative;
    height: 8px;
    margin: 1rem 0 0.375rem;
    background: rgba(var(--empp-primary-rgb), 0.12);
    border-radius: 4px;
    overflow: hidden;
  }
  .porosity-scale span {
    position: absolute;
    inset: 0 auto 0 0;
    background: var(--empp-primary);
    border-radius: 4px;
  }
  .porosity-ticks {
    display: flex;
    justify-content: space-between;
    font-family: var(--empp-mono);
    font-size: 0.6875rem;
    color: var(--empp-muted);
  }
  .porosity-hero p {
    margin: 0.75rem 0 0;
  }

  /* One-time reveal on load: the bar fills and a soft ring fades out */
  @media (prefers-reduced-motion: no-preference) {
    .porosity-hero {
      animation: porosity-ring 1.6s ease-out 0.2s 1 both;
    }
    .porosity-scale span {
      animation: porosity-fill 1.1s cubic-bezier(0.2, 0.7, 0.2, 1) 0.2s 1 both;
    }
  }
  @keyframes porosity-fill {
    from { width: 0; }
  }
  @keyframes porosity-ring {
    0%   { box-shadow: 0 0 0 0 rgba(var(--empp-primary-rgb), 0.35); }
    100% { box-shadow: 0 0 0 14px rgba(var(--empp-primary-rgb), 0); }
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <a href="<?= $is_owner ? '/dashboard/researcher/research-studies' : '/dashboard/administrator/research-studies'; ?>" class="d-inline-flex align-items-center mb-3">
      <i class="bx bx-chevron-left"></i> <?= $is_owner ? 'My research studies' : 'All research studies'; ?>
    </a>

    <div class="card mb-4">
      <div class="card-body d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div style="max-width: 70ch;">
          <span class="empp-eyebrow">Research study #<?= (int) $research->__get('ree_id'); ?></span>
          <h4 class="mb-1"><?= htmlspecialchars($research->__get('ree_name')); ?></h4>
          <?php if ($research->__get('ree_description')) { ?>
            <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($research->__get('ree_description'))); ?></p>
          <?php } ?>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
          <span class="empp-eyebrow">Created <?= htmlspecialchars(substr($research->__get('ree_create'), 0, 16)); ?></span>
          <?php if (!$is_owner) { ?>
            <span class="badge bg-label-primary">Viewing as administrator &middot; study by <?= htmlspecialchars($research->__get('res_name')); ?></span>
          <?php } ?>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Sample -->
      <div class="col-lg-5">
        <figure class="study-image mb-4">
          <img src="<?= $_ENV['BASE_IMG']; ?>research/<?= rawurlencode($research->__get('ree_file')); ?>" alt="SEM image of <?= htmlspecialchars($research->__get('ree_name')); ?>">
          <figcaption>SEM image &middot; uploaded <?= htmlspecialchars(substr($research->__get('ree_create'), 0, 16)); ?></figcaption>
        </figure>

        <span class="empp-eyebrow mb-2">Electrospinning parameters</span>
        <div class="readout-grid">
          <div>
            <span class="empp-eyebrow">Flow rate</span>
            <div class="empp-readout"><?= htmlspecialchars($research->__get('ree_flow')); ?><span class="empp-unit">mL/min</span></div>
          </div>
          <div>
            <span class="empp-eyebrow">Applied voltage</span>
            <div class="empp-readout"><?= htmlspecialchars($research->__get('ree_voltage')); ?><span class="empp-unit">kV</span></div>
          </div>
          <div>
            <span class="empp-eyebrow">Needle–collector</span>
            <div class="empp-readout"><?= htmlspecialchars($research->__get('ree_distance')); ?><span class="empp-unit">cm</span></div>
          </div>
        </div>
      </div>

      <!-- Analysis -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Analysis</h5>
          </div>
          <div class="card-body">
            <ol class="pipeline">

              <!-- 1. Haralick -->
              <li class="pipeline-step <?= $has_features ? 'is-done' : 'is-next'; ?>">
                <span class="pipeline-marker"><?= $has_features ? '<i class="bx bx-check"></i>' : '01'; ?></span>
                <div class="pipeline-title">
                  <h6>Haralick texture features</h6>
                  <span class="pipeline-status" id="step1-status"><?= $has_features ? 'Done' : 'Pending'; ?></span>
                </div>
                <div class="pipeline-body">
                  <?php if ($has_features) { ?>
                    <dl class="haralick-grid mb-0">
                      <?php foreach ($haralick as $label => $value) { ?>
                        <div>
                          <dt><?= $label; ?></dt>
                          <dd><?= number_format((float) $value, 4, '.', ''); ?></dd>
                        </div>
                      <?php } ?>
                    </dl>
                  <?php } elseif ($is_owner) { ?>
                    <p>Extracts the texture features from the SEM image and, in the same run, predicts the membrane porosity from them.</p>
                    <button class="btn btn-primary" id="analysisBtn">
                      <i class="bx bx-play-circle me-1"></i> Analyse image &amp; predict porosity
                    </button>
                    <p class="small mt-2 mb-0" id="analysis-progress" aria-live="polite"></p>
                  <?php } else { ?>
                    <p>The researcher has not processed this image yet.</p>
                  <?php } ?>
                </div>
              </li>

              <!-- 2. Porosity -->
              <li class="pipeline-step <?= $has_porosity ? 'is-done' : ($has_features ? 'is-next' : ''); ?>">
                <span class="pipeline-marker"><?= $has_porosity ? '<i class="bx bx-check"></i>' : '02'; ?></span>
                <div class="pipeline-title">
                  <h6>Predicted porosity</h6>
                  <span class="pipeline-status" id="step2-status"><?= $has_porosity ? 'Done' : ($has_features ? 'Ready' : 'Pending'); ?></span>
                </div>
                <div class="pipeline-body">
                  <?php if ($has_porosity) {
                    $pct = max(0, min(100, (float) $porosity)); ?>
                    <div class="porosity-hero">
                      <span class="empp-eyebrow">Key result &middot; membrane porosity</span>
                      <div class="empp-readout porosity-readout" id="predicted-porosity"
                           data-value="<?= number_format((float) $porosity, 2, '.', ''); ?>"><span class="porosity-number"><?= number_format((float) $porosity, 2); ?></span><span class="empp-unit">%</span></div>
                      <div class="porosity-scale" role="img" aria-label="Porosity <?= number_format($pct, 1); ?> percent"><span style="width: <?= $pct; ?>%"></span></div>
                      <div class="porosity-ticks"><span>0%</span><span>50%</span><span>100%</span></div>
                      <p>Estimated by the EMPP regression model from the process parameters and texture features.</p>
                    </div>
                    <script>
                    // Count up to the predicted value once, in step with the bar (skipped for reduced motion)
                    (function () {
                      var box = document.getElementById('predicted-porosity');
                      var num = box.querySelector('.porosity-number');
                      var target = parseFloat(box.getAttribute('data-value'));
                      if (isNaN(target) || !window.matchMedia || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                      var start = null, delay = 200, duration = 1100;
                      function frame(t) {
                        if (start === null) start = t;
                        var p = Math.min(Math.max((t - start - delay) / duration, 0), 1);
                        var eased = 1 - Math.pow(1 - p, 3);
                        num.textContent = (target * eased).toFixed(2);
                        if (p < 1) requestAnimationFrame(frame);
                      }
                      num.textContent = (0).toFixed(2);
                      requestAnimationFrame(frame);
                    })();
                    </script>
                  <?php } elseif ($has_features && $is_owner) { ?>
                    <?php // Features saved but no prediction (e.g. the prediction failed in a previous run): retry only this step ?>
                    <p>The texture features are ready. Run the porosity model with them and the process parameters.</p>
                    <button class="btn btn-primary" id="predict-porosity-button">Predict porosity</button>
                  <?php } elseif ($has_features) { ?>
                    <p>The researcher has not run the prediction yet.</p>
                  <?php } elseif ($is_owner) { ?>
                    <p>Runs automatically right after step 1.</p>
                  <?php } else { ?>
                    <p>Needs the texture features from step 1.</p>
                  <?php } ?>
                </div>
              </li>

              <!-- 3. Filtration simulation -->
              <li class="pipeline-step <?= $simulation ? 'is-done' : ($has_porosity ? 'is-next' : ''); ?>">
                <span class="pipeline-marker"><?= $simulation ? '<i class="bx bx-check"></i>' : '03'; ?></span>
                <div class="pipeline-title">
                  <h6>Filtration performance simulation</h6>
                  <span class="pipeline-status"><?= $simulation ? 'Done' : 'Optional'; ?></span>
                </div>
                <div class="pipeline-body">
                  <?php if ($simulation) { ?>
                    <p>Pressure drop, collection efficiency and quality factor for this membrane.</p>
                    <a class="btn btn-outline-primary" href="/dashboard/researcher/research/filter-simulation-view/<?= (int) $simulation->fis_id; ?>">View simulation</a>
                  <?php } elseif ($is_owner) { ?>
                    <p>Simulate pressure drop, collection efficiency and quality factor for this membrane.</p>
                    <a class="btn btn-outline-primary" href="/dashboard/researcher/research/filter-simulation-create/<?= (int) $research->__get('ree_id'); ?>">Set up simulation</a>
                  <?php } else { ?>
                    <p class="mb-0">No simulation for this study.</p>
                  <?php } ?>
                </div>
              </li>
            </ol>

            <div id="response1"></div>
            <div id="response2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->


<script>
async function processHaralick() {
    const button = document.getElementById('haralickBtn');
    const originalText = button.textContent;
    const imageId = "<?= (int) $research->__get('ree_id'); ?>";
    const imageUrl = <?= json_encode($empp_image_url, $empp_js_flags); ?>;

    try {
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        button.disabled = true;

        // 1. Call Haralick API
        // Via proxy same-origin: chamada direta ao rapzap era barrada por CORS.
        const haralickResponse = await fetch('/api_proxy.php?target=haralick', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ image_url: imageUrl, image_id: imageId })
        });

        const responseData = await haralickResponse.json();
        console.log('Haralick response:', responseData);

        if (!haralickResponse.ok) {
            throw new Error(`Haralick API error ${haralickResponse.status}: ${responseData.detail || 'Unknown error'}`);
        }

        const features = responseData.haralick_features;

        // 2. Prepare data ensuring no value is null
        const dbPostData = {
            fk_research_ree_id: imageId,
            rre_correlation: parseFloat(features.Correlation ?? features.correlation ?? 0),
            rre_dissimilarity: parseFloat(features.Dissimilarity ?? features.dissimilarity ?? 0),
            rre_energy: parseFloat(features.Energy ?? features.energy ?? 0),
            rre_homogeneity: parseFloat(features.Homogeneity ?? features.homogeneity ?? 0),
        };

        // Ensure all values are valid numbers
        Object.keys(dbPostData).forEach(key => {
            if (dbPostData[key] === null || dbPostData[key] === undefined || isNaN(dbPostData[key])) {
                dbPostData[key] = 0;
            }
        });

        console.log('Database payload:', dbPostData);

        // 3. Save to database using FormData (more compatible with PHP)
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        
        // Convert to FormData for better PHP compatibility
        const formData = new FormData();
        formData.append('fk_research_ree_id', dbPostData.fk_research_ree_id);
        formData.append('rre_correlation', dbPostData.rre_correlation);
        formData.append('rre_dissimilarity', dbPostData.rre_dissimilarity);
        formData.append('rre_energy', dbPostData.rre_energy);
        formData.append('rre_homogeneity', dbPostData.rre_homogeneity);

        

        const dbResponse = await fetch('/dashboard/researcher/research/insertResult', {
            method: 'POST',
            body: formData
        });

        const responseText = await dbResponse.text();
        console.log('Database response:', responseText);

        if (!dbResponse.ok) {
            throw new Error(`Database error ${dbResponse.status}: ${responseText}`);
        }

        // Check if response contains success
        if (responseText.includes('Erro') || responseText.includes('error')) {
            throw new Error(responseText);
        }

        showNotification('success', '✅ Features processed and saved successfully!');
        
        // Redirect after success (if needed)
        setTimeout(() => {
            window.location.href = "<?= $_ENV['BASE_URL'] ?>dashboard/researcher/research/view/" + imageId;
        }, 2000);

    } catch (error) {
        console.error('Process error:', error);
        showNotification('error', `❌ Error: ${error.message}`);
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = alert;
    document.body.appendChild(tempDiv.firstElementChild);
    
    setTimeout(() => {
        const alertElement = document.querySelector('.alert.position-fixed');
        if (alertElement) alertElement.remove();
    }, 5000);
}

document.getElementById('haralickBtn')?.addEventListener('click', processHaralick);
</script>

<script>
document.getElementById('predict-porosity-button')?.addEventListener('click', predictPorosity);

async function predictPorosity() {
    const button = this;
    const originalText = button.textContent;
    
    try {
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Calculating...';
        button.disabled = true;

        // Get values with null value handling
        const getValue = (value, defaultValue = 0) => {
            return value !== null && value !== undefined ? value : defaultValue;
        };

        // Modelo novo (MLflow /invocations): 7 features em ingles, formato dataframe_split.
        // NAO usa mais Id, Composition, Rotation, Translation (o modelo os ignora).
        const porosityFeatures = {
            Syringe_flow_rate: parseFloat(getValue(<?= json_encode((float) $research->__get('ree_flow')); ?>, 0)),
            Tension: parseFloat(getValue(<?= json_encode((float) $research->__get('ree_voltage')); ?>, 0)),
            Distance: parseFloat(getValue(<?= json_encode((float) $research->__get('ree_distance')); ?>, 0)),
            Dissimilarity: parseFloat(getValue(<?= $results ? json_encode((float) $results->__get('rre_dissimilarity')) : 'null'; ?>, 0)),
            Correlation: parseFloat(getValue(<?= $results ? json_encode((float) $results->__get('rre_correlation')) : 'null'; ?>, 0)),
            Energy: parseFloat(getValue(<?= $results ? json_encode((float) $results->__get('rre_energy')) : 'null'; ?>, 0)),
            Homogeneity: parseFloat(getValue(<?= $results ? json_encode((float) $results->__get('rre_homogeneity')) : 'null'; ?>, 0))
        };

        const porosityColumns = ["Syringe_flow_rate", "Tension", "Distance", "Dissimilarity", "Correlation", "Energy", "Homogeneity"];
        const porosityData = {
            dataframe_split: {
                columns: porosityColumns,
                data: [porosityColumns.map(c => porosityFeatures[c])]
            }
        };

        console.log('Porosity request data:', porosityData);

        const response = await fetch('/api_proxy.php?target=porosity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(porosityData)
        });

        const responseText = await response.text();
        console.log('Raw API response:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            throw new Error(`Invalid JSON response: ${responseText}`);
        }

        if (!response.ok) {
            throw new Error(`API error ${response.status}: ${result.message || responseText}`);
        }
        // Modelo novo devolve {"predictions":[valor]}; mantem fallback pro formato antigo.
        const predictedPorosity = Array.isArray(result.predictions) ? result.predictions[0] : result.predicted_porosity;
        console.log("Result: ", predictedPorosity);
        // Show result
        if(savePorosityResult(predictedPorosity)){
            showNotification('success', '✅ Features processed and saved successfully!');
        
            // Redirect after success (if needed)
            setTimeout(() => {
                window.location.href = "<?= $_ENV['BASE_URL'] ?>dashboard/researcher/research/view/<?= (int) $research->__get('ree_id'); ?>";
            }, 2000);
        }

    } catch (error) {
        console.error('Porosity prediction failed:', error);
        showNotification('error', `❌ ${error.message}`);
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

function displayPorosityResult(result) {
    // Try to extract porosity value in different ways
    const porosity = result.Porosity || result.porosity || result.prediction || 
                    result.result || result.value || result.data;
    
    if (porosity !== undefined) {
        const formattedPorosity = typeof porosity === 'number' ? porosity.toFixed(2) : porosity;
        
        // Show notification
        showNotification('success', `✅ Predicted Porosity: ${formattedPorosity}%`);
        
        // Update UI
        updatePorosityDisplay(formattedPorosity);
        console.log("porosity: ".formattedPorosity);
        // Optionally: save to database
        savePorosityResult(formattedPorosity);
    } else {
        showNotification('info', '✅ Prediction completed. Check console for details.');
        console.log('Full result:', result);
    }
}

function updatePorosityDisplay(porosityValue) {
    // Update or create element to show porosity
    let displayElement = document.getElementById('porosity-display');
    
    if (!displayElement) {
        displayElement = document.createElement('div');
        displayElement.id = 'porosity-display';
        displayElement.className = 'card mt-3';
        displayElement.innerHTML = `
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Porosity Prediction Result</h5>
            </div>
            <div class="card-body">
                <p class="display-4 text-center">${porosityValue}%</p>
            </div>
        `;
        document.getElementById('predict-porosity-button').insertAdjacentElement('afterend', displayElement);
    } else {
        displayElement.querySelector('.display-4').textContent = `${porosityValue}%`;
    }
}

async function savePorosityResult(porosityValue) {
    // Optionally: save porosity result to database
    try {
        const researchId = "<?= (int) $research->__get('ree_id'); ?>";
        const formData = new FormData();
        formData.append('fk_research_ree_id', researchId);
        formData.append('rre_porosity', porosityValue);
        
        const response = await fetch('/dashboard/researcher/research/updateporosity', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            return true;
        }
    } catch (error) {
        console.warn('Could not save porosity to database:', error);
    }
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = alert;
    document.body.appendChild(tempDiv.firstElementChild);
    
    setTimeout(() => {
        const alertElement = document.querySelector('.alert.position-fixed');
        if (alertElement) alertElement.remove();
    }, 5000);
}
</script>

<script>
/**
 * One run: extract the Haralick features, save them, predict the porosity
 * from them and save it. If the prediction fails after the features were
 * saved, the reloaded page offers "Predict porosity" to retry only that step.
 */
(function () {
    const button = document.getElementById('analysisBtn');
    if (!button) return;

    const study = {
        id: <?= (int) $research->__get('ree_id'); ?>,
        imageUrl: <?= json_encode($empp_image_url, $empp_js_flags); ?>,
        flow: <?= json_encode((float) $research->__get('ree_flow')); ?>,
        voltage: <?= json_encode((float) $research->__get('ree_voltage')); ?>,
        distance: <?= json_encode((float) $research->__get('ree_distance')); ?>
    };
    const viewUrl = '/dashboard/researcher/research/view/' + study.id;
    const progress = document.getElementById('analysis-progress');
    const status1 = document.getElementById('step1-status');
    const status2 = document.getElementById('step2-status');
    const originalLabel = button.innerHTML;

    function setProgress(step, text) {
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + step + '/2 ' + text;
        progress.textContent = step === 1
            ? 'Extracting texture features from the image. This can take up to a minute for large images.'
            : 'Texture features saved. Running the porosity model.';
        status1.textContent = step === 1 ? 'Running…' : 'Done';
        status2.textContent = step === 2 ? 'Running…' : 'Pending';
    }

    // The save endpoints answer with a redirect; don't follow it, just check it wasn't an error
    async function post(url, fields) {
        const body = new FormData();
        Object.keys(fields).forEach(function (k) { body.append(k, fields[k]); });
        const res = await fetch(url, { method: 'POST', body: body, redirect: 'manual' });
        if (!(res.ok || res.type === 'opaqueredirect')) {
            throw new Error('Could not save the result (HTTP ' + res.status + ').');
        }
    }

    function number(value) {
        const n = parseFloat(value);
        return isNaN(n) ? 0 : n;
    }

    async function run() {
        button.disabled = true;
        let featuresSaved = false;
        try {
            // 1. Haralick features
            setProgress(1, 'Extracting texture features…');
            const hRes = await fetch('/api_proxy.php?target=haralick', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ image_url: study.imageUrl, image_id: String(study.id) })
            });
            const hData = await hRes.json().catch(function () { return {}; });
            if (!hRes.ok || !hData.haralick_features) {
                throw new Error('Texture feature extraction failed' + (hData.detail || hData.error ? ': ' + (hData.detail || hData.error) : '.'));
            }
            const f = hData.haralick_features;
            const features = {
                Dissimilarity: number(f.Dissimilarity ?? f.dissimilarity),
                Correlation: number(f.Correlation ?? f.correlation),
                Energy: number(f.Energy ?? f.energy),
                Homogeneity: number(f.Homogeneity ?? f.homogeneity)
            };
            await post('/dashboard/researcher/research/insertResult', {
                fk_research_ree_id: study.id,
                rre_dissimilarity: features.Dissimilarity,
                rre_correlation: features.Correlation,
                rre_energy: features.Energy,
                rre_homogeneity: features.Homogeneity
            });
            featuresSaved = true;

            // 2. Porosity prediction (same feature contract as the MLflow model)
            setProgress(2, 'Predicting porosity…');
            const columns = ['Syringe_flow_rate', 'Tension', 'Distance', 'Dissimilarity', 'Correlation', 'Energy', 'Homogeneity'];
            const values = [study.flow, study.voltage, study.distance, features.Dissimilarity, features.Correlation, features.Energy, features.Homogeneity];
            const pRes = await fetch('/api_proxy.php?target=porosity', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ dataframe_split: { columns: columns, data: [values] } })
            });
            const pData = await pRes.json().catch(function () { return {}; });
            const porosity = Array.isArray(pData.predictions) ? pData.predictions[0] : pData.predicted_porosity;
            if (!pRes.ok || porosity === undefined || isNaN(parseFloat(porosity))) {
                throw new Error('Porosity prediction failed' + (pData.message || pData.error ? ': ' + (pData.message || pData.error) : '.'));
            }
            await post('/dashboard/researcher/research/updateporosity', {
                fk_research_ree_id: study.id,
                rre_porosity: porosity
            });

            status2.textContent = 'Done';
            progress.textContent = 'Done. Loading the results…';
            window.location.href = viewUrl;
        } catch (err) {
            console.error('Analysis failed:', err);
            if (featuresSaved) {
                // Step 1 is stored: reload so the page shows the features and a retry for step 2 only
                await Swal.fire({
                    title: 'Porosity prediction failed',
                    text: 'The texture features were saved. ' + err.message + ' You can retry the prediction on its own.',
                    icon: 'warning'
                });
                window.location.href = viewUrl;
                return;
            }
            Swal.fire({ title: 'Analysis failed', text: err.message + ' Nothing was saved; please try again.', icon: 'error' });
            button.innerHTML = originalLabel;
            button.disabled = false;
            progress.textContent = '';
            status1.textContent = 'Pending';
            status2.textContent = 'Pending';
        }
    }

    button.addEventListener('click', run);
})();
</script>