<?php include __DIR__ . '/../includes/auth/head.php'; ?>

    <div class="auth-shell">
      <?php include __DIR__ . '/../includes/auth/panel.php'; ?>

      <main class="auth-main">
        <div class="auth-form">
          <img class="auth-form-logo" src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="EMPP" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" />

          <span class="empp-eyebrow">Sign in</span>
          <h2>Welcome back</h2>
          <p class="auth-lead">Sign in to access your research studies.</p>

          <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger" role="alert">Invalid email or password.</div>
          <?php } ?>

          <form id="formAuthentication" action="/signin" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email or username</label>
              <input
                type="text"
                class="form-control"
                id="email"
                name="email-username"
                placeholder="you@institution.edu"
                autocomplete="username"
                autofocus
                required
              />
            </div>
            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                <a href="/forgot-password"><small>Forgot password?</small></a>
              </div>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password"
                  autocomplete="current-password"
                  required
                />
                <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
              </div>
            </div>
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" />
                <label class="form-check-label" for="remember-me">Keep me signed in</label>
              </div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Sign in</button>
          </form>

          <p class="auth-switch">
            New to EMPP? <a href="/sign-up">Create a researcher account</a>
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
