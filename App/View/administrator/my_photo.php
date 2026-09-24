<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="account-page">
      <?php include __DIR__ . '/../includes/dashboard/account_header.php'; ?>

      <div class="card">
        <div class="card-body">
          <form id="formAuthentication" action="/dashboard/administrator/upload-photo" method="POST" enctype="multipart/form-data">
            <input type="file" id="profile-photo" name="profile_photo" accept="image/jpeg, image/png, image/webp" class="d-none">
            <input type="hidden" name="fk_login_log_id" value="<?= (int) $_SESSION['log_id'] ?>">

            <label for="profile-photo" class="empp-dropzone" id="photo-dropzone" style="min-height: 200px;">
              <i class="bx bx-user-circle"></i>
              <strong>Choose a new profile photo</strong>
              <span>JPG, PNG or WEBP &middot; up to 2 MB &middot; square images work best</span>
            </label>

            <div id="preview-container" class="text-center" style="display: none;">
              <img id="new-photo-preview" class="rounded-circle mb-3" alt="New profile photo"
                   style="width: 160px; height: 160px; object-fit: cover; border: 1px solid var(--empp-line);">
              <div class="d-flex justify-content-center gap-2">
                <button type="button" id="cancel-upload" class="btn btn-outline-secondary">Choose another</button>
                <button type="submit" class="btn btn-primary">Save photo</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

<script>
$(document).ready(function() {
  const input = $('#profile-photo');
  const dropzone = $('#photo-dropzone');
  const previewContainer = $('#preview-container');

  input.change(function() {
    const file = this.files && this.files[0];
    if (!file) return;
    if (!validateImage(file)) {
      $(this).val('');
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      $('#new-photo-preview').attr('src', e.target.result);
      dropzone.hide();
      previewContainer.show();
    };
    reader.readAsDataURL(file);
  });

  $('#cancel-upload').click(function() {
    input.val('');
    previewContainer.hide();
    dropzone.show();
  });

  function validateImage(file) {
    if (file.size > 2 * 1024 * 1024) {
      Swal.fire({ title: 'File too large', text: 'The maximum size is 2 MB.', icon: 'error' });
      return false;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      Swal.fire({ title: 'Invalid file type', text: 'Use a JPG, PNG or WEBP image.', icon: 'error' });
      return false;
    }
    return true;
  }

  const messages = {
    '0': { title: 'Could not update the photo', text: 'Please try again.', icon: 'error' },
    '1': { title: 'Photo updated', text: 'Your new profile photo is saved.', icon: 'success' }
  };
  const status = new URLSearchParams(window.location.search).get('success');
  if (messages[status]) {
    history.replaceState(null, '', window.location.pathname);
    Swal.fire(Object.assign({ timer: 3000, timerProgressBar: true, showConfirmButton: false }, messages[status]));
  }
});
</script>
