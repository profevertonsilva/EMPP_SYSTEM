<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="account-page">
      <?php include __DIR__ . '/../includes/dashboard/account_header.php'; ?>

      <div class="card">
        <div class="card-body">
          <form id="formAuthentication" action="/dashboard/administrator/update-my-password" method="POST">
            <div class="mb-4 form-password-toggle">
              <label for="current_password" class="form-label">Current password</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
              </div>
            </div>
            <div class="row g-3 mb-4">
              <div class="col-md-6 form-password-toggle">
                <label for="new_password" class="form-label">New password</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
                  <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
                </div>
                <div class="form-text">At least 8 characters.</div>
              </div>
              <div class="col-md-6 form-password-toggle">
                <label for="confirm_new_password" class="form-label">Confirm new password</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" autocomplete="new-password" required>
                  <span class="input-group-text cursor-pointer" aria-label="Show password"><i class="bx bx-hide"></i></span>
                </div>
                <div class="small text-danger mt-1 d-none" id="mismatch">Passwords don't match.</div>
              </div>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-primary" type="submit">Update password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

<script>
$(document).ready(function() {
  // Catch a mismatch before it reaches the server
  $('#formAuthentication').on('submit', function(e) {
    const same = $('#new_password').val() === $('#confirm_new_password').val();
    $('#mismatch').toggleClass('d-none', same);
    $('#confirm_new_password').toggleClass('is-invalid', !same);
    if (!same) e.preventDefault();
  });

  // Result codes from actionUpdateMyPassword
  const messages = {
    '1': { title: 'Password updated', text: 'Use the new password next time you sign in.', icon: 'success' },
    '0': { title: 'Current password is incorrect', text: 'Check it and try again.', icon: 'error' },
    '2': { title: 'Could not update the password', text: 'Please try again.', icon: 'error' },
    '3': { title: 'Passwords don\'t match', text: 'The new password and its confirmation must be the same.', icon: 'warning' }
  };
  const status = new URLSearchParams(window.location.search).get('success');
  if (messages[status]) {
    history.replaceState(null, '', window.location.pathname);
    Swal.fire(Object.assign({ timer: 3500, timerProgressBar: true, showConfirmButton: false }, messages[status]));
  }
});
</script>
