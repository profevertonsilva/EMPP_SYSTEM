<?php
$auth_extra_head = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"
      integrity="sha384-OXVF05DQEe311p6ohU11NwlnX08FzMCsyoXzGOaL+83dKAb3qS17yZJxESl8YrJQ" crossorigin="anonymous" />';
include __DIR__ . '/../includes/auth/head.php';
?>

    <div class="auth-shell">
      <?php include __DIR__ . '/../includes/auth/panel.php'; ?>

      <main class="auth-main">
        <div class="auth-form auth-form-wide">
          <img class="auth-form-logo" src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="EMPP" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" />

          <span class="empp-eyebrow">Researcher account</span>
          <h2>Create your account</h2>
          <p class="auth-lead">Tell us who you are and how you plan to use EMPP.</p>

          <form id="formAuthentication" action="/signup" method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Full name</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Your full name" autocomplete="name" required />
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="institution" class="form-label">Institution</label>
                <input type="text" class="form-control" id="institution" name="institution" placeholder="University or company" autocomplete="organization" required />
              </div>
              <div class="col-md-6">
                <label for="academic" class="form-label">Academic degree</label>
                <select class="form-select" id="academic" name="academic" required>
                  <option value="">Select your degree</option>
                  <option value="Undergraduate Student">Undergraduate Student</option>
                  <option value="Bachelor">Bachelor</option>
                  <option value="Master">Master</option>
                  <option value="PHD Degree">PhD</option>
                  <option value="other">Other</option>
                </select>
                <input type="text" class="form-control mt-2" id="specific" name="specific" placeholder="Specify your degree" style="display:none" />
              </div>
            </div>

            <div class="mb-3">
              <label for="purpose" class="form-label">Purpose</label>
              <textarea class="form-control" id="purpose" name="purpose" placeholder="What will you use EMPP for?" rows="3" required></textarea>
            </div>

            <div class="mb-3">
              <label for="country" class="form-label">Country</label>
              <select class="form-control form-select" id="country" name="country" required>
                <option value="">Select your country</option>
                <?php foreach ($this->getView()->countries as $country): ?>
                <option value="<?= $country->cou_id; ?>"><?= $country->cou_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@institution.edu" autocomplete="email" required />
              </div>
              <div class="col-md-6 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" autocomplete="new-password" required />
                  <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required />
                <label class="form-check-label" for="terms-conditions">
                  I agree to the
                  <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">privacy policy &amp; terms</a>
                </label>
              </div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Create account</button>
          </form>

          <p class="auth-switch">
            Already have an account? <a href="/sign-in">Sign in</a>
          </p>
        </div>
      </main>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="termsModalLabel">Privacy policy &amp; terms</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4>1. Privacy Policy</h4>
            <p>
              [Insert the full text of your privacy policy here. Example:
              We value your privacy and are committed to protecting your personal information. This policy describes how we collect, use, and share your information when you use our platform...]
            </p>

            <h4>2. Terms of Use</h4>
            <p>
              [Insert the full text of your terms of use here. Example:
              By accessing or using our platform, you agree to be bound by these Terms of Use. If you do not agree with any part of the terms, you must not access the platform...]
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Core JS -->
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/jquery/jquery.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/popper/popper.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/bootstrap.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/menu.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
      integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
      integrity="sha384-6LwNpGeYDjlORU0Q5rfxEC8SQO6/FTh/VecUcvFvNx1gLMdX5dm8y1Y739D3lFSW" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
      integrity="sha384-d3UHjPdzJkZuk5H3qKYMLRyWLAQBJbby2yr2Q58hXXtAGF8RSNO9jpLDlKKPv5v3" crossorigin="anonymous"></script>

    <script src="<?= $_ENV['BASE_JS']; ?>main.js"></script>
    <script>
    $(document).ready(function() {
        // Searchable country field
        $('#country').select2({
          placeholder: "Type to search your country...",
          allowClear: true
        });

        // Free-text degree when "Other" is selected
        $('#academic').change(function() {
            if ($(this).val() === 'other') {
                $('#specific').show();
            } else {
                $('#specific').hide();
                $('#specific').val('');
            }
        });
    });
    </script>
  </body>
</html>
