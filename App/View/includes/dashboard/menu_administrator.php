<?php
// Administrators are researchers with oversight: their own work first, then administration.
$empp_menu_active = $this->getView()->active_page ?? '';
$empp_menu_item = function ($key, $href, $icon, $label) use ($empp_menu_active) {
  $active = $empp_menu_active === $key ? ' active' : '';
  echo '<li class="menu-item' . $active . '">'
     . '<a href="' . $href . '" class="menu-link">'
     . '<i class="menu-icon ' . $icon . '"></i>'
     . '<div>' . $label . '</div>'
     . '</a></li>';
};
?>
<!-- Menu -->

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="/dashboard/administrator" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_b.png" alt="Logo" title="<?= $_ENV['SITE_TITLE_HOME']; ?>" />
      </span>

    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    <li class="menu-header"><span class="menu-header-text">My work</span></li>
    <?php
    $empp_menu_item('dashboard', '/dashboard/researcher', 'bx bx-home-circle', 'Dashboard');
    $empp_menu_item('research', '/dashboard/researcher/research-studies', 'fa-solid fa-book-open-reader', 'My Research Studies');
    ?>

    <li class="menu-header"><span class="menu-header-text">Administration</span></li>
    <?php
    $empp_menu_item('overview', '/dashboard/administrator', 'bx bx-pulse', 'Overview');
    $empp_menu_item('researchers', '/dashboard/administrator/researchers', 'fa-solid fa-user-tie', 'Researchers');
    $empp_menu_item('all_research', '/dashboard/administrator/research-studies', 'fa-solid fa-layer-group', 'All Research Studies');
    $empp_menu_item('dataset', '/dashboard/administrator/dataset', 'fa-solid fa-images', 'Image Dataset');
    ?>

    <li class="menu-header"><span class="menu-header-text">Account</span></li>
    <?php $empp_menu_item('logout', '/signout', 'fa-solid fa-power-off', 'Logout'); ?>
  </ul>

</aside>
<!-- / Menu -->
<!-- Layout container -->
<div class="layout-page">
