<?php
// Step indicator for the "new research" flow. Set $empp_step (1 or 2) before including.
// The page title names the flow; the indicator names the step, so neither repeats the other.
$empp_steps = [1 => 'SEM image', 2 => 'Electrospinning parameters'];
?>
<div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
  <div>
    <a href="/dashboard/researcher/research-studies" class="d-inline-flex align-items-center mb-2">
      <i class="bx bx-chevron-left"></i> My research studies
    </a>
    <h4 class="mb-0">New research study</h4>
  </div>
  <ol class="empp-steps" aria-label="Progress">
    <?php foreach ($empp_steps as $n => $label) {
      $state = $n < $empp_step ? 'is-done' : ($n === $empp_step ? 'is-current' : ''); ?>
      <li class="<?= $state; ?>"<?= $n === $empp_step ? ' aria-current="step"' : ''; ?>>
        <span aria-hidden="true"><?= $n < $empp_step ? '<i class="bx bx-check"></i>' : sprintf('%02d', $n); ?></span><?= $label; ?><?= $n < $empp_step ? '<small class="visually-hidden"> (done)</small>' : ''; ?>
      </li>
    <?php } ?>
  </ol>
</div>
