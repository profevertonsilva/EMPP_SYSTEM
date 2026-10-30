<?php include __DIR__ . '/../includes/auth/head.php'; ?>

    <div class="auth-shell">
      <?php include __DIR__ . '/../includes/auth/panel.php'; ?>

      <main class="auth-main">
        <div class="auth-form">
          <a href="/" aria-label="EMPP home"><img class="auth-form-logo" src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="EMPP" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" /></a>

          <span class="empp-eyebrow">Password reset</span>
          <h2>Forgot your password?</h2>
          <p class="auth-lead">Enter the email you signed up with and we'll send you a 6-digit code to set a new password.</p>

          <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger" role="alert">That email address is not valid.</div>
          <?php } ?>

          <form id="formAuthentication" action="/forgot-password/send" method="POST">
            <div class="mb-4">
              <label for="email" class="form-label">E-mail</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="you@institution.edu"
                autocomplete="email"
                value="<?= htmlspecialchars($_SESSION['reset']['email'] ?? ''); ?>"
                autofocus
                required
              />
            </div>
            <button class="btn btn-primary w-100" type="submit">Send code</button>
          </form>

          <p class="auth-switch">
            <a href="/sign-in" class="d-inline-flex align-items-center">
              <i class="bx bx-chevron-left scaleX-n1-rtl"></i>
              Back to sign in
            </a>
          </p>
        </div>
      </main>
    </div>

    <!-- Core JS -->
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/jquery/jquery.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/popper/popper.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/bootstrap.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/menu.js"></script>
    <script src="<?= $_ENV['BASE_JS']; ?>main.js"></script>
  </body>
</html>
