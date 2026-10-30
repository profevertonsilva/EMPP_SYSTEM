<?php
/*
 * Public page about the research (/research). Every section comes from
 * System settings in the administrator menu and is left out when empty.
 */
$v = $this->getView();
$res = $v->research;
$logged = !empty($v->logged);
$e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
$img = function ($name) { return $_ENV['BASE_IMG'] . 'site/' . rawurlencode($name); };
// Free text: blank lines separate paragraphs, single line breaks are kept
$paras = function ($text) use ($e) {
    $out = '';
    foreach (preg_split('/\R\s*\R/', trim((string) $text)) as $p) {
        if (trim($p) !== '') {
            $out .= '<p>' . nl2br($e(trim($p))) . '</p>';
        }
    }
    return $out;
};

$researchers = array_values(array_filter($res['people'], function ($p) { return $p['per_role'] === 'researcher'; }));
$supervisors = array_values(array_filter($res['people'], function ($p) { return $p['per_role'] === 'supervisor'; }));
$types = ['university' => 'University', 'department' => 'Department', 'other' => 'Partner'];
$institutions = $res['institutions'];
usort($institutions, function ($a, $b) {
    // Universities first, then departments, then other entities; admin order within each
    $rank = ['university' => 0, 'department' => 1, 'other' => 2];
    return [$rank[$a['ins_type']], (int) $a['ins_order']] <=> [$rank[$b['ins_type']], (int) $b['ins_order']];
});
$footer_name = $researchers[0]['per_name'] ?? 'EMPP';

// Profile links, in the order a researcher reads them
$profile_links = ['per_lattes' => 'Lattes', 'per_orcid' => 'ORCID', 'per_researchgate' => 'ResearchGate',
                  'per_linkedin' => 'LinkedIn', 'per_instagram' => 'Instagram', 'per_website' => 'Website'];

$person = function ($p) use ($e, $img, $profile_links) {
    $initials = implode('', array_map(function ($w) { return mb_strtoupper(mb_substr($w, 0, 1)); },
                array_slice(preg_split('/\s+/', trim($p['per_name'])), 0, 2)));
    ob_start(); ?>
    <li class="rs-person">
      <span class="rs-photo">
        <?php if ($p['per_photo']) { ?>
          <img src="<?= $e($img($p['per_photo'])); ?>" alt="" loading="lazy">
        <?php } else { ?>
          <span aria-hidden="true"><?= $e($initials); ?></span>
        <?php } ?>
      </span>
      <div class="rs-person-body">
        <h4><?= $e($p['per_name']); ?></h4>
        <?php if ($p['per_title']) { ?><p class="rs-person-title"><?= $e($p['per_title']); ?></p><?php } ?>
        <?php if ($p['entity']) { $ent = $p['entity']; ?>
          <p class="rs-entity">
            <?php if ($ent['ins_logo']) { ?><img src="<?= $e($img($ent['ins_logo'])); ?>" alt="" loading="lazy"><?php } ?>
            <?php if ($ent['ins_url']) { ?>
              <a href="<?= $e($ent['ins_url']); ?>" rel="noopener" target="_blank"><?= $e($ent['ins_name']); ?></a>
            <?php } else { ?>
              <span><?= $e($ent['ins_name']); ?></span>
            <?php } ?>
          </p>
        <?php } ?>
        <?php if ($p['per_email'] || $p['per_phone']) { ?>
          <p class="rs-contact">
            <?php if ($p['per_email']) { ?><a href="mailto:<?= $e($p['per_email']); ?>"><?= $e($p['per_email']); ?></a><?php } ?>
            <?php if ($p['per_phone']) { ?><a href="tel:<?= $e(preg_replace('/[^\d+]/', '', $p['per_phone'])); ?>"><?= $e($p['per_phone']); ?></a><?php } ?>
          </p>
        <?php } ?>
        <?php $links = array_filter(array_intersect_key($p, $profile_links)); if ($links) { ?>
          <ul class="rs-links" aria-label="Profiles of <?= $e($p['per_name']); ?>">
            <?php foreach ($profile_links as $col => $label) { if (!empty($p[$col])) { ?>
              <li><a href="<?= $e($p[$col]); ?>" rel="noopener" target="_blank"><?= $label; ?></a></li>
            <?php } } ?>
          </ul>
        <?php } ?>
      </div>
    </li>
    <?php return ob_get_clean();
};

$page = 'research';
$page_title = $v->title_page;
$page_desc = 'The research behind EMPP: team, supervisors, institutions, publications, projects and funding.';
include __DIR__ . '/partials/top.php';
?>
    <div class="site-hero site-wrap rs-hero">
      <h1>The research</h1>
      <?php if (trim($res['texts']['motivation']) !== '') { ?>
        <div class="site-lead rs-motivation"><?= $paras($res['texts']['motivation']); ?></div>
      <?php } else { ?>
        <p class="site-lead">EMPP comes from doctoral research on electrospun membranes: predicting porosity from SEM images and simulating how the membrane filters.</p>
      <?php } ?>
      <?php if ($institutions) { ?>
        <ul class="rs-hero-logos" aria-label="Institutions">
          <?php foreach ($institutions as $ins) { if ($ins['ins_type'] === 'other') continue; ?>
            <li>
              <?php if ($ins['ins_logo']) { ?><span class="rs-logo-chip"><img src="<?= $e($img($ins['ins_logo'])); ?>" alt="" loading="lazy"></span><?php } ?>
              <span><?= $e($ins['ins_acronym'] ?: $ins['ins_name']); ?></span>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </header>

  <main id="main">
    <?php if ($researchers || $supervisors) { ?>
      <section class="site-section site-wrap" id="team" aria-labelledby="team-title">
        <div class="site-section-head">
          <h2 id="team-title">Team</h2>
        </div>
        <div class="rs-team">
          <?php foreach (['Researchers' => $researchers, 'Supervisors' => $supervisors] as $label => $group) { if (!$group) continue; ?>
            <div class="rs-team-group">
              <h3><?= $label; ?></h3>
              <ul class="rs-people">
                <?php foreach ($group as $p) { echo $person($p); } ?>
              </ul>
            </div>
          <?php } ?>
        </div>
      </section>
    <?php } ?>

    <?php if ($institutions) { ?>
      <section class="site-section site-wrap" id="institutions" aria-labelledby="ins-title">
        <div class="site-section-head">
          <h2 id="ins-title">Institutions</h2>
        </div>
        <ul class="rs-orgs">
          <?php foreach ($institutions as $ins) { ?>
            <li>
              <span class="rs-org-logo">
                <?php if ($ins['ins_logo']) { ?><img src="<?= $e($img($ins['ins_logo'])); ?>" alt="" loading="lazy"><?php } ?>
              </span>
              <span class="rs-org-type"><?= $types[$ins['ins_type']]; ?></span>
              <span class="rs-org-name">
                <?php if ($ins['ins_url']) { ?>
                  <a href="<?= $e($ins['ins_url']); ?>" rel="noopener" target="_blank"><?= $e($ins['ins_name']); ?></a>
                <?php } else { ?>
                  <?= $e($ins['ins_name']); ?>
                <?php } ?>
                <?php if ($ins['ins_acronym']) { ?><small><?= $e($ins['ins_acronym']); ?></small><?php } ?>
              </span>
            </li>
          <?php } ?>
        </ul>
      </section>
    <?php } ?>

    <?php if ($res['publications']) { ?>
      <section class="site-section site-wrap" id="publications" aria-labelledby="pub-title">
        <div class="site-section-head">
          <h2 id="pub-title">Publications</h2>
        </div>
        <ol class="rs-pubs">
          <?php foreach ($res['publications'] as $pub) {
            $doi = trim((string) $pub['pub_doi']);
            $doi = preg_replace('#^(https?://(dx\.)?doi\.org/|doi:)#i', '', $doi);
            $href = $pub['pub_url'] ?: ($doi ? 'https://doi.org/' . $doi : null); ?>
            <li>
              <span class="rs-year"><?= $e($pub['pub_year']); ?></span>
              <div>
                <cite>
                  <?php if ($href) { ?><a href="<?= $e($href); ?>" rel="noopener" target="_blank"><?= $e($pub['pub_title']); ?></a><?php } else { ?><?= $e($pub['pub_title']); ?><?php } ?>
                </cite>
                <?php if ($pub['pub_authors']) { ?><p class="rs-authors"><?= $e($pub['pub_authors']); ?></p><?php } ?>
                <p class="rs-venue">
                  <?= $e($pub['pub_venue']); ?>
                  <?php if ($doi) { ?><a class="rs-doi" href="https://doi.org/<?= $e($doi); ?>" rel="noopener" target="_blank">doi:<?= $e($doi); ?></a><?php } ?>
                </p>
              </div>
            </li>
          <?php } ?>
        </ol>
      </section>
    <?php } ?>

    <?php if ($res['projects']) { ?>
      <section class="site-section site-wrap" id="projects" aria-labelledby="pro-title">
        <div class="site-section-head">
          <h2 id="pro-title">Projects</h2>
        </div>
        <ol class="rs-projects">
          <?php foreach ($res['projects'] as $pro) { ?>
            <li>
              <span class="rs-year"><?= $e($pro['pro_period']); ?></span>
              <div>
                <h3>
                  <?php if ($pro['pro_url']) { ?><a href="<?= $e($pro['pro_url']); ?>" rel="noopener" target="_blank"><?= $e($pro['pro_title']); ?></a><?php } else { ?><?= $e($pro['pro_title']); ?><?php } ?>
                </h3>
                <?php if ($pro['pro_description']) { ?><div class="rs-text"><?= $paras($pro['pro_description']); ?></div><?php } ?>
              </div>
            </li>
          <?php } ?>
        </ol>
      </section>
    <?php } ?>

    <?php if ($res['funders']) { ?>
      <section class="site-section site-wrap" id="funding" aria-labelledby="fun-title">
        <div class="site-section-head">
          <h2 id="fun-title">Funding</h2>
        </div>
        <ul class="rs-orgs rs-funders">
          <?php foreach ($res['funders'] as $f) { ?>
            <li>
              <span class="rs-org-logo">
                <?php if ($f['fun_logo']) { ?><img src="<?= $e($img($f['fun_logo'])); ?>" alt="" loading="lazy"><?php } ?>
              </span>
              <span class="rs-org-name">
                <?php if ($f['fun_url']) { ?>
                  <a href="<?= $e($f['fun_url']); ?>" rel="noopener" target="_blank"><?= $e($f['fun_name']); ?></a>
                <?php } else { ?>
                  <?= $e($f['fun_name']); ?>
                <?php } ?>
                <?php if ($f['fun_grant']) { ?><small class="empp-num"><?= $e($f['fun_grant']); ?></small><?php } ?>
              </span>
            </li>
          <?php } ?>
        </ul>
      </section>
    <?php } ?>

    <?php if (trim($res['texts']['acknowledgements']) !== '') { ?>
      <section class="site-section site-wrap" id="acknowledgements" aria-labelledby="ack-title">
        <div class="site-section-head rs-text">
          <h2 id="ack-title">Acknowledgements</h2>
          <?= $paras($res['texts']['acknowledgements']); ?>
        </div>
      </section>
    <?php } ?>

<?php
$scripts = [];
include __DIR__ . '/partials/bottom.php';
