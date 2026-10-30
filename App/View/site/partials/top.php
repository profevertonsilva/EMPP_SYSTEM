<?php
/*
 * Public site: document head, skip link and the deep field's nav.
 * Expects $v (view), $e (escaper), $logged, and:
 *   $page         'home' | 'research' (marks the current nav item)
 *   $page_title   <title>
 *   $page_desc    meta description
 *   $extra_head   optional raw HTML for <head>
 * Leaves <header class="site-field"> open: the page adds its hero and closes it.
 */
$home = $page === 'home' ? '' : '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($page_title); ?></title>
  <meta name="description" content="<?= $e($page_desc); ?>">
  <meta name="theme-color" content="#1a2547">
  <link rel="icon" type="image/png" href="<?= $_ENV['BASE_URL']; ?>resources/img/icone_empp.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $_ENV['BASE_CSS']; ?>empp.css">
  <link rel="stylesheet" href="<?= $_ENV['BASE_CSS']; ?>site.css">
  <?= $extra_head ?? ''; ?>
</head>
<body class="site">
  <a class="site-skip" href="#main">Skip to content</a>

  <header class="site-field">
    <nav class="site-nav site-wrap" aria-label="Main">
      <a class="site-logo" href="/" aria-label="EMPP home">
        <img src="<?= $_ENV['BASE_URL']; ?>resources/img/logo_empp_w.png" alt="EMPP — Electrospun Membrane Property Predictor" width="2299" height="705">
      </a>
      <ul class="site-links">
        <li><a href="<?= $home; ?>#method">How it works</a></li>
        <li><a href="<?= $home; ?>#simulation">Simulation</a></li>
        <li><a href="/research"<?= $page === 'research' ? ' aria-current="page"' : ''; ?>>The research</a></li>
      </ul>
      <div class="site-nav-actions">
        <?php if ($logged) { ?>
          <a class="site-btn site-btn-light" href="<?= $e($v->dashboard_url); ?>">Open dashboard</a>
        <?php } else { ?>
          <a class="site-signin" href="/sign-in">Sign in</a>
          <a class="site-btn site-btn-light" href="/sign-up">Create free account</a>
        <?php } ?>
      </div>
    </nav>
