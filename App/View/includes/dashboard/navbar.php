<?php
$empp_is_admin = $_SESSION['log_type'] == 'A';
$empp_me = $this->getView()->researcher;
$empp_photo = $empp_me ? $empp_me->photoUrl() : null;
$empp_avatar_inner = $empp_photo
  ? '<img src="' . htmlspecialchars($empp_photo) . '" alt class="w-px-40 h-auto rounded-circle" />'
  : '<span class="avatar-initial rounded-circle bg-label-primary">' . htmlspecialchars($empp_me ? $empp_me->initial() : '') . '</span>';
$empp_avatar_state = $_SESSION['log_status'] == 'A' ? 'avatar-online' : 'avatar-offline';
$empp_avatar_title = $_SESSION['log_status'] == 'A' ? '' : ' title="User not activated"';

// Section name shown in the navbar, from the active menu item
$empp_sections = [
  'dashboard'    => 'Dashboard',
  'research'     => 'My Research Studies',
  'overview'     => 'Overview',
  'researchers'  => 'Researchers',
  'all_research' => 'All Research Studies',
  'dataset'      => 'Image Dataset',
];
$empp_active = $this->getView()->active_page ?? '';
$empp_title = $this->getView()->title ?? '';
if (isset($empp_sections[$empp_active])) {
  $empp_section = $empp_sections[$empp_active];
} elseif (in_array($this->getView()->page ?? '', ['my_profile', 'my_password', 'my_photo'])) {
  $empp_section = 'Account';
} elseif ($empp_title !== '' && $empp_title !== 'EMPP') {
  $empp_section = $empp_title;
} else {
  $empp_section = 'Electrospun Membrane Property Predictor';
}
?>
<!-- Navbar -->

<nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" aria-label="Open menu">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <div class="empp-navbar-title">
                <span class="empp-eyebrow"><?= $empp_is_admin ? 'Administrator' : 'Researcher'; ?> workspace</span>
                <span class="empp-page"><?= htmlspecialchars($empp_section); ?></span>
              </div>

              <ul class="navbar-nav flex-row align-items-center ms-auto">

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center gap-3" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <span class="empp-user-meta d-none d-md-block">
                      <span class="empp-user-name"><?= $this->getView()->researcher->res_name; ?></span>
                      <span class="empp-eyebrow"><?= $empp_is_admin ? 'Admin' : 'Researcher'; ?></span>
                    </span>
                    <div class="avatar <?= $empp_avatar_state; ?>"<?= $empp_avatar_title; ?>>
                      <?= $empp_avatar_inner; ?>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar <?= $empp_avatar_state; ?>"<?= $empp_avatar_title; ?>>
                              <?= $empp_avatar_inner; ?>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block"><?= $this->getView()->researcher->res_name; ?></span>
                            <small class="text-muted"><?= $empp_is_admin ? 'Admin' : 'Researcher'; ?></small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/dashboard/administrator/my-profile">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/dashboard/administrator/my-password">
                        <i class="fa-solid fa-key me-2"></i>
                        <span class="align-middle">Update My Password</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/dashboard/administrator/my-photo">
                        <i class="fa-solid fa-image me-2"></i>
                        <span class="align-middle">Update My Photo</span>
                      </a>
                    </li>

                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/signout">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->
