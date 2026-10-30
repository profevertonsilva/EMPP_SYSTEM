<?php include __DIR__ . '/../includes/auth/head.php'; ?>
<?php
$reset = $this->getView()->reset;
$resendIn = $this->getView()->resendIn;
$errors = [
    'code' => 'That code is not correct. Check the email and try again.',
    'expired' => 'This code has expired. Send a new one below.',
    'attempts' => 'Too many incorrect tries. Send a new code below.',
    'send' => 'We could not send the email right now. Try sending the code again in a minute.',
    'password-empty' => 'Type a new password.',
    'password-match' => 'The passwords do not match. Type the same password in both fields.',
];
$errorKey = $_GET['error'] ?? '';
$error = $errors[$errorKey] ?? null;
?>

    <div class="auth-shell">
      <?php include __DIR__ . '/../includes/auth/panel.php'; ?>

      <main class="auth-main">
        <div class="auth-form">
          <a href="/" aria-label="EMPP home"><img class="auth-form-logo" src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="EMPP" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" /></a>

          <h2>Set a new password</h2>
          <p class="auth-lead">
            If <strong class="verify-email-address"><?= htmlspecialchars($reset['email']); ?></strong> has an EMPP account, a 6-digit code is on its way.
            It is valid for <?= $this->getView()->minutes; ?> minutes.
          </p>

          <?php if ($error) { ?>
            <div class="alert alert-danger" role="alert"><?= $error; ?></div>
          <?php } elseif (isset($_GET['resent'])) { ?>
            <div class="alert alert-success" role="status">A new code is on its way. The previous one no longer works.</div>
          <?php } ?>

          <?php include __DIR__ . '/../includes/auth/spam_note.php'; ?>

          <form id="formReset" action="/reset-password/confirm" method="POST">
            <div class="mb-3">
              <label for="code" class="form-label">Code from the email</label>
              <input
                type="text"
                class="form-control verify-code-input<?= in_array($errorKey, ['code', 'expired', 'attempts']) ? ' is-invalid' : ''; ?>"
                id="code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                autofocus
                required
              />
            </div>
            <div class="mb-3 form-password-toggle">
              <label class="form-label" for="password">New password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" autocomplete="new-password" required />
                <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
              </div>
            </div>
            <div class="mb-4 form-password-toggle">
              <label class="form-label" for="password_confirm">Confirm new password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password_confirm" class="form-control<?= $errorKey === 'password-match' ? ' is-invalid' : ''; ?>" name="password_confirm" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password_confirm-feedback" autocomplete="new-password" required />
                <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
              </div>
              <div id="password_confirm-feedback" class="invalid-feedback<?= $errorKey === 'password-match' ? ' d-block' : ''; ?>">The passwords do not match.</div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Save new password</button>
          </form>

          <form action="/reset-password/resend" method="POST" class="verify-resend">
            <span>Still nothing?</span>
            <button type="submit" class="btn btn-link p-0" id="resend" <?= $resendIn > 0 ? 'disabled' : ''; ?> data-wait="<?= (int) $resendIn; ?>">
              Send a new code<span class="verify-resend-wait"><?= $resendIn > 0 ? " in {$resendIn}s" : ''; ?></span>
            </button>
          </form>

          <p class="auth-switch">
            Wrong email? <a href="/forgot-password">Use another address</a>
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

      // Confirm password, checked before the form is sent
      (function () {
        var password = document.getElementById('password');
        var confirm = document.getElementById('password_confirm');
        var feedback = document.getElementById('password_confirm-feedback');
        function check() {
          var mismatch = confirm.value !== '' && confirm.value !== password.value;
          confirm.classList.toggle('is-invalid', mismatch);
          feedback.classList.toggle('d-block', mismatch);
          confirm.setCustomValidity(mismatch ? 'The passwords do not match.' : '');
        }
        password.addEventListener('input', function () { if (confirm.value !== '') check(); });
        confirm.addEventListener('input', check);
        confirm.addEventListener('blur', check);
      })();

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
