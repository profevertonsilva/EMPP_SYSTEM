<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Register Card -->
      <div class="card">
        <div class="card-body">
          <?php $edit = $this->getView()->researchers; $edit_photo = $edit->photoUrl(); ?>
          <a href="/dashboard/administrator/researchers/<?= (int) $edit->fk_login_log_id; ?>" class="d-inline-flex align-items-center mb-3">
            <i class="bx bx-chevron-left"></i> Back to researcher
          </a>
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar avatar-lg flex-shrink-0">
              <?php if ($edit_photo) { ?>
                <img src="<?= htmlspecialchars($edit_photo); ?>" alt="" class="rounded-circle">
              <?php } else { ?>
                <span class="avatar-initial rounded-circle bg-label-primary"><?= htmlspecialchars($edit->initial()); ?></span>
              <?php } ?>
            </div>
            <div>
              <span class="empp-eyebrow">Edit researcher</span>
              <h5 class="mb-0"><?= htmlspecialchars($edit->res_name); ?></h5>
            </div>
          </div>
          


          <form id="formAuthentication" class="mb-3" action="/dashboard/administrator/update-profile" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Enter your Name" value="<?= $this->getView()->researchers->res_name; ?>" required />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Institution</label>
              <input type="text" class="form-control" id="institution" name="institution" placeholder="Enter your Institution" value="<?= $this->getView()->researchers->res_institution; ?>" required />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Academic Degree</label>
              <select class="form-select" id="academic" name="academic" required>
                  <option value="">Select your Academic Degree</option>
                  <option value="Undergraduate Student" <?= ($this->getView()->researchers->res_academic == "Undergraduate Student") ? "selected" : "" ?>>Undergraduate Student</option>
                  <option value="Bachelor" <?= ($this->getView()->researchers->res_academic == "Bachelor") ? "selected" : "" ?>>Bachelor</option>
                  <option value="Master" <?= ($this->getView()->researchers->res_academic == "Master") ? "selected" : "" ?>>Master</option>
                  <option value="PHD Degree" <?= ($this->getView()->researchers->res_academic == "PHD Degree") ? "selected" : "" ?>>PHD Degree</option>
                  <option value="other" <?= (!in_array($this->getView()->researchers->res_academic, ["Undergraduate Student", "Bachelor", "Master", "PHD Degree"])) ? "selected" : "" ?>>Others</option>
              </select>
              <input 
                  type="text" 
                  class="form-control" 
                  id="specific" 
                  name="specific" 
                  value="<?= (!in_array($this->getView()->researchers->res_academic, ["Undergraduate Student", "Bachelor", "Master", "PHD Degree"])) ? $this->getView()->researchers->res_academic : '' ?>" 
                  placeholder="Enter your Specific Degree" 
                  style="display: none" 
              />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Purpose</label>
              <textarea class="form-control" id="purpose" name="purpose" placeholder="Enter your Purpose" required/><?= $this->getView()->researchers->res_purpose; ?></textarea>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Country</label>
              <select class="form-select" id="country" name="country" required>
                <option value="">Select your country</option>
                <?php foreach ($this->getView()->country as $country): ?>
                  <option value="<?= $country->cou_id; ?>" <?php if ($country->cou_id === $this->getView()->researchers->fk_country_cou_id) {
                                                              echo 'selected';
                                                            } ?>><?= $country->cou_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>


            <!-- The researcher being edited (not the signed-in admin) -->
            <input type="hidden" name="res_id" value="<?= (int) $this->getView()->researchers->res_id; ?>" />
            <button class="btn btn-primary d-grid w-100">Update Research Profile</button>
          </form>


        </div>
      </div>
      <!-- Register Card -->
    </div>
  </div>
</div>

<!-- / Content -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
  integrity="sha384-6LwNpGeYDjlORU0Q5rfxEC8SQO6/FTh/VecUcvFvNx1gLMdX5dm8y1Y739D3lFSW" crossorigin="anonymous"></script>
<script>

  $(document).ready(function() {
    // Check if URL has success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');

    if (successParam === '1') {
      // Remove the parameter from URL immediately (before showing alert)
      const cleanURL = window.location.pathname + window.location.hash;
      history.replaceState(null, '', cleanURL);

      // Now show the alert
      Swal.fire({
        title: 'Success!',
        text: 'Your profile was updated successfully',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true
      });
    }
  });
  $(document).ready(function() {
    // Monitora mudanças no select de academic
    $('#academic').change(function() {
        if ($(this).val() === 'other') {
            $('#specific').show();
        } else {
            $('#specific').hide();
            $('#specific').val(''); // Limpa o campo se não for "other"
        }
    });

    // Verifica o valor inicial ao carregar a página
    if ($('#academic').val() === 'other') {
        $('#specific').show();
    } else {
        $('#specific').hide();
    }
});
</script>