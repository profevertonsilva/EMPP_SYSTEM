<!DOCTYPE html>
<!--
  Auth pages (sign in, sign up, forgot password).
  Built on Sneat - Bootstrap 5 HTML Admin Template (ThemeSelection), themed by empp.css.
  Optional before include: $auth_extra_head (string of extra <link>/<script> tags).
-->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= $_ENV['BASE_ASSETS']; ?>"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= $this->getView()->title_page; ?></title>

    <meta name="description" content="EMPP predicts electrospun membrane porosity and filtration performance from SEM images and electrospinning parameters." />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $_ENV['BASE_URL']; ?>resources/img/icone_empp.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="<?= $_ENV['BASE_VENDOR']; ?>fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= $_ENV['BASE_VENDOR']; ?>css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= $_ENV['BASE_VENDOR']; ?>css/theme-default.css" class="template-customizer-theme-css" />

    <?= $auth_extra_head ?? ''; ?>

    <!-- EMPP theme: must stay last so it overrides template and vendor CSS -->
    <link rel="stylesheet" href="<?= $_ENV['BASE_CSS']; ?>empp.css" />

    <!-- Helpers -->
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/helpers.js"></script>
    <script src="<?= $_ENV['BASE_JS']; ?>config.js"></script>
  </head>

  <body>
