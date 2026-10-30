<?php include __DIR__ . '/../includes/auth/head.php'; ?>
<?php
$verify = $this->getView()->verify;
$resendIn = $this->getView()->resendIn;
$errors = [
    'code' => 'That code is not correct. Check the email and try again.',
    'expired' => 'This code has expired. Send a new one below.',
    'attempts' => 'Too many incorrect tries. Send a new code below.',
    'send' => 'We could not send the email right now. Try sending the code again in a minute.',
];
$error = $errors[$_GET['error'] ?? ''] ?? null;
?>

    <div class="auth-shell">
      <?php include __DIR__ . '/../includes/auth/panel.php'; ?>

      <main class="auth-main">
        <div class="auth-form">
          <a href="/" aria-label="EMPP home"><img class="auth-form-logo" src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="EMPP" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" /></a>

          <h2>Check your email</h2>
          <p class="auth-lead">
            We sent a 6-digit code to <strong class="verify-email-address"><?= htmlspecialchars($verify['email']); ?></strong>.
            Type it below to activate your account. It is valid for <?= $this->getView()->minutes; ?> minutes.
          </p>

          <?php if ($error) { ?>
            <div class="alert alert-danger" role="alert"><?= $error; ?></div>
          <?php } elseif (isset($_GET['resent'])) { ?>
            <div class="alert alert-success" role="status">A new code is on its way. The previous one no longer works.</div>
          <?php } ?>

          <?php include __DIR__ . '/../includes/auth/spam_note.php'; ?>

          <form action="/verify-email/confirm" method="POST">
            <div class="mb-4">
              <label for="code" class="form-label">Verification code</label>
              <input
                type="text"
                class="form-control verify-code-input"
                id="code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                aria-describedby="code-help"
                autofocus
                required
              />
              <div id="code-help" class="form-text">Only the 6 digits; spaces are ignored.</div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Confirm email</button>
          </form>

          <form action="/verify-email/resend" method="POST" class="verify-resend">
            <span>Still nothing?</span>
            <button type="submit" class="btn btn-link p-0" id="resend" <?= $resendIn > 0 ? 'disabled' : ''; ?> data-wait="<?= (int) $resendIn; ?>">
              Send a new code<span class="verify-resend-wait"><?= $resendIn > 0 ? " in {$resendIn}s" : ''; ?></span>
            </button>
          </form>

          <p class="auth-switch">
            Already confirmed? <a href="/sign-in">Sign in</a>
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
    <script>
      // Digits only, so a pasted "123 456" still fits the 6 characters
      document.getElementById('code').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
      });

      // Countdown until another code may be requested
      (function () {
        var btn = document.getElementById('resend');
        var wait = btn.querySelector('.verify-resend-wait');
        var left = parseInt(btn.dataset.wait, 10);
        if (left <= 0) return;
        var timer = setInterval(function () {
          left -= 1;
          if (left <= 0) {
            clearInterval(timer);
            wait.textContent = '';
            btn.disabled = false;
          } else {
            wait.textContent = ' in ' + left + 's';
          }
        }, 1000);
      })();
    </script>
  </body>
</html>
