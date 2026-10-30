<!-- Shown wherever a code was just emailed: providers often file it as spam -->
<div class="auth-note" role="note">
  <i class="bx bx-envelope" aria-hidden="true"></i>
  <p>
    <strong>Not in your inbox?</strong>
    Check your spam or junk folder. The email comes from <span class="auth-note-sender"><?= htmlspecialchars($_ENV['MAIL_FROM']); ?></span>; marking it as "not spam" helps the next ones arrive.
  </p>
</div>
