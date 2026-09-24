<?php
$me = $this->getView()->researcher;
$known_degrees = ["Undergraduate Student", "Bachelor", "Master", "PHD Degree"];
$is_other = !in_array($me->res_academic, $known_degrees);
?>
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="account-page">
      <?php include __DIR__ . '/../includes/dashboard/account_header.php'; ?>

      <div class="card">
        <div class="card-body">
          <form id="formAuthentication" action="/dashboard/account/update-profile" method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Full name</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($me->res_name); ?>" autocomplete="name" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="institution" class="form-label">Institution</label>
                <input type="text" class="form-control" id="institution" name="institution" value="<?= htmlspecialchars($me->res_institution); ?>" autocomplete="organization" required>
              </div>
              <div class="col-md-6">
                <label for="academic" class="form-label">Academic degree</label>
                <select class="form-select" id="academic" name="academic" required>
                  <option value="">Select your degree</option>
                  <option value="Undergraduate Student" <?= $me->res_academic == "Undergraduate Student" ? "selected" : "" ?>>Undergraduate Student</option>
                  <option value="Bachelor" <?= $me->res_academic == "Bachelor" ? "selected" : "" ?>>Bachelor</option>
                  <option value="Master" <?= $me->res_academic == "Master" ? "selected" : "" ?>>Master</option>
                  <option value="PHD Degree" <?= $me->res_academic == "PHD Degree" ? "selected" : "" ?>>PhD</option>
                  <option value="other" <?= $is_other ? "selected" : "" ?>>Other</option>
                </select>
                <input type="text" class="form-control mt-2" id="specific" name="specific"
                       value="<?= $is_other ? htmlspecialchars($me->res_academic) : '' ?>"
                       placeholder="Specify your degree" style="<?= $is_other ? '' : 'display: none'; ?>">
              </div>
            </div>
            <div class="mb-3">
              <label for="purpose" class="form-label">Purpose</label>
              <textarea class="form-control" id="purpose" name="purpose" rows="3" required><?= htmlspecialchars($me->res_purpose); ?></textarea>
            </div>
            <div class="mb-4">
              <label for="country" class="form-label">Country</label>
              <select class="form-select" id="country" name="country" required>
                <option value="">Select your country</option>
                <?php foreach ($this->getView()->countries as $country): ?>
                  <option value="<?= $country->cou_id; ?>" <?= $country->cou_id == $me->fk_country_cou_id ? 'selected' : ''; ?>><?= htmlspecialchars($country->cou_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

<script>
$(document).ready(function() {
  // Free-text degree when "Other" is selected
  $('#academic').change(function() {
    if ($(this).val() === 'other') {
      $('#specific').show();
    } else {
      $('#specific').hide().val('');
    }
  });

  const status = new URLSearchParams(window.location.search).get('success');
  if (status !== null) {
    history.replaceState(null, '', window.location.pathname);
    Swal.fire({
      title: status === '1' ? 'Profile updated' : 'Could not save',
      text: status === '1' ? 'Your changes were saved.' : 'Please try again.',
      icon: status === '1' ? 'success' : 'error',
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false
    });
  }
});
</script>
