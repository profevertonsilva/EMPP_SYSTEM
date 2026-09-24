<?php
// Header + tabs shared by the account pages (my_profile, my_password, my_photo)
$acc_me = $this->getView()->researcher;
$acc_photo = $acc_me ? $acc_me->photoUrl() : null;
$acc_page = $this->getView()->page ?? '';
$acc_tabs = [
  'my_profile'  => ['/dashboard/administrator/my-profile', 'Profile'],
  'my_password' => ['/dashboard/administrator/my-password', 'Password'],
  'my_photo'    => ['/dashboard/administrator/my-photo', 'Photo'],
];
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <div class="avatar avatar-xl flex-shrink-0">
    <?php if ($acc_photo) { ?>
      <img src="<?= htmlspecialchars($acc_photo); ?>" alt="" class="rounded-circle">
    <?php } else { ?>
      <span class="avatar-initial rounded-circle bg-label-primary fs-3"><?= htmlspecialchars($acc_me ? $acc_me->initial() : ''); ?></span>
    <?php } ?>
  </div>
  <div>
    <span class="empp-eyebrow">Account</span>
    <h4 class="mb-0"><?= htmlspecialchars($acc_me ? $acc_me->res_name : ''); ?></h4>
    <small class="text-muted"><?= htmlspecialchars($acc_me ? (string) $acc_me->log_email : ''); ?></small>
  </div>
</div>
<ul class="nav account-tabs mb-4">
  <?php foreach ($acc_tabs as $key => [$href, $label]) { ?>
    <li class="nav-item"><a class="nav-link<?= $acc_page === $key ? ' active' : ''; ?>" href="<?= $href; ?>"><?= $label; ?></a></li>
  <?php } ?>
</ul>
