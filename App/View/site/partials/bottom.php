<?php
/*
 * Public site: closing call to action, </main>, footer and scripts.
 * Expects $v, $e, $logged, $footer_name and optional $scripts (list of JS URLs).
 */
?>
    <!-- Close -->
    <section class="site-close" aria-labelledby="close-title">
      <div class="site-wrap">
        <h2 id="close-title">Run your first study</h2>
        <p>Bring an SEM image of your membrane and the parameters you spun it with.</p>
        <div class="site-hero-actions">
          <?php if ($logged) { ?>
            <a class="site-btn site-btn-light" href="<?= $e($v->dashboard_url); ?>">Open dashboard</a>
          <?php } else { ?>
            <a class="site-btn site-btn-light" href="/sign-up">Create free account</a>
            <a class="site-btn site-btn-ghost" href="/sign-in">Sign in</a>
          <?php } ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="site-wrap">
      <img src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_w.png" alt="EMPP" width="2299" height="705">
      <p>Electrospun Membrane Property Predictor · © <?= date('Y'); ?> <?= $e($footer_name); ?> · <a href="/research">The research</a></p>
    </div>
  </footer>

  <?php foreach ($scripts ?? [] as $src) { ?>
    <script src="<?= $e($src); ?>" defer></script>
  <?php } ?>
</body>
</html>
