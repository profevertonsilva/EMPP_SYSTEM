<?php
use App\DAO\SiteContentDAO;

$v = $this->getView();
$tab = $v->tab;
$content = $v->content;
$editing = $v->editing;
$csrf = $v->csrf;
$e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
$img = function ($name) { return $name ? $_ENV['BASE_IMG'] . 'site/' . rawurlencode($name) : null; };

$tabs = [];
foreach (SiteContentDAO::SECTIONS as $key => $s) {
    $tabs[$key] = ['label' => $s['label'], 'count' => count($content[$key])];
}
$tabs['texts'] = ['label' => 'Motivation & thanks', 'count' => null];

$messages = [
    'saved' => ['success', 'Saved. The public page shows the change now.'],
    'deleted' => ['success', 'Deleted.'],
    'required' => ['warning', 'Fill in the required fields (marked *).'],
    'email' => ['warning', 'Enter a valid e-mail address.'],
    'url' => ['warning', 'One of the links is not a valid web address.'],
    'year' => ['warning', 'The year must have four digits, e.g. 2025.'],
    'image' => ['warning', 'The image was not saved. Use PNG, JPG or WEBP up to 2 MB.'],
    'invalid' => ['warning', 'One of the choices is not valid. Please try again.'],
    'expired' => ['warning', 'The form expired. Please try again.'],
];
$status = $messages[$_GET['status'] ?? ''] ?? null;

// One-line summary of an item for the list
$summary = function ($key, $row) use ($content) {
    switch ($key) {
        case 'institutions':
            $types = SiteContentDAO::SECTIONS['institutions']['fields']['ins_type']['options'];
            return [$row['ins_name'], trim($types[$row['ins_type']] . ($row['ins_acronym'] ? ' · ' . $row['ins_acronym'] : ''))];
        case 'people':
            $roles = SiteContentDAO::SECTIONS['people']['fields']['per_role']['options'];
            $entity = $content['institutions_by_id'][(int) $row['fk_site_institution_ins_id']]['ins_name'] ?? null;
            return [$row['per_name'], implode(' · ', array_filter([$roles[$row['per_role']], $entity, $row['per_email']]))];
        case 'publications':
            return [$row['pub_title'], implode(' · ', array_filter([$row['pub_venue'], $row['pub_year']]))];
        case 'projects':
            return [$row['pro_title'], (string) $row['pro_period']];
        case 'funders':
            return [$row['fun_name'], (string) $row['fun_grant']];
    }
    return ['', ''];
};
?>
<style>
  .settings-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--empp-line);
  }
  .settings-tabs a {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.125rem;
    margin-bottom: -1px;
    border-bottom: 2px solid transparent;
    color: var(--empp-text);
    font-weight: 500;
    text-decoration: none;
  }
  .settings-tabs a:hover {
    color: var(--empp-ink);
  }
  .settings-tabs a[aria-current='page'] {
    color: var(--empp-primary);
    border-bottom-color: var(--empp-primary);
  }
  .settings-tabs .count {
    min-width: 1.375rem;
    padding: 0 0.375rem;
    border-radius: var(--empp-radius-sm);
    background: var(--empp-bg);
    color: var(--empp-text);
    font-size: 0.6875rem;
    font-variant-numeric: tabular-nums;
    text-align: center;
  }
  .settings-tabs a[aria-current='page'] .count {
    background: var(--empp-primary-soft);
    color: var(--empp-primary);
  }

  .settings-list {
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .settings-list li {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--empp-line);
  }
  .settings-list li:last-child {
    border-bottom: 0;
  }
  .settings-list li.settings-group-row {
    display: block;
    padding: 0.5rem 1.25rem;
    background: var(--empp-bg);
    color: var(--empp-text);
    font-size: 0.8125rem;
    font-weight: 600;
  }
  .settings-list li.is-editing {
    background: var(--empp-primary-soft);
  }
  .settings-thumb {
    display: grid;
    place-items: center;
    width: 3rem;
    height: 3rem;
    border: 1px solid var(--empp-line);
    border-radius: var(--empp-radius-sm);
    background: #fff;
    overflow: hidden;
    color: var(--empp-muted);
  }
  .settings-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .settings-thumb.is-photo {
    border-radius: 50%;
  }
  .settings-thumb.is-photo img {
    object-fit: cover;
  }
  .settings-list .title {
    display: block;
    color: var(--empp-ink);
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .settings-list .meta {
    display: block;
    color: var(--empp-muted);
    font-size: 0.8125rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .settings-actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }
  .settings-actions form {
    margin: 0;
  }
  .settings-actions .btn {
    padding: 0.25rem 0.5rem;
  }
  .settings-empty {
    padding: 2.5rem 1.25rem;
    color: var(--empp-muted);
    text-align: center;
  }

  .settings-form {
    top: 6rem;
  }
  /* Hints: the template's pale grey falls short of 4.5:1 on white */
  .settings-form .form-text,
  .settings-empty {
    color: var(--empp-muted);
  }
  .settings-group {
    margin: 1.25rem 0 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid var(--empp-line);
    color: var(--empp-ink);
    font-size: 0.8125rem;
    font-weight: 600;
  }
  .settings-group:first-child {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
  }
  .settings-image {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  .settings-image .settings-thumb {
    width: 4rem;
    height: 4rem;
    flex: none;
  }
</style>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1">System settings</h4>
        <p class="mb-0 text-muted">Everything here appears on the public page <a href="/research" target="_blank" rel="noopener">/research</a>. Empty sections are not shown.</p>
      </div>
      <a class="btn btn-outline-primary" href="/research" target="_blank" rel="noopener">
        View public page <i class="bx bx-link-external ms-1" aria-hidden="true"></i>
      </a>
    </div>

    <nav class="settings-tabs" aria-label="Settings sections">
      <?php foreach ($tabs as $key => $t) { ?>
        <a href="?tab=<?= $key; ?>"<?= $key === $tab ? ' aria-current="page"' : ''; ?>>
          <?= $e($t['label']); ?>
          <?php if ($t['count'] !== null) { ?><span class="count"><?= $t['count']; ?></span><?php } ?>
        </a>
      <?php } ?>
    </nav>

    <?php if ($status) { ?>
      <div class="alert alert-<?= $status[0]; ?> mb-4" role="<?= $status[0] === 'success' ? 'status' : 'alert'; ?>"><?= $e($status[1]); ?></div>
    <?php } ?>

    <?php if ($tab === 'texts') { ?>
      <!-- Free texts -->
      <form class="card" method="POST" action="/dashboard/administrator/settings/save">
        <input type="hidden" name="csrf" value="<?= $e($csrf); ?>">
        <input type="hidden" name="tab" value="texts">
        <div class="card-body">
          <div class="mb-4">
            <label class="form-label" for="motivation">Motivation</label>
            <textarea class="form-control" id="motivation" name="motivation" rows="6" aria-describedby="motivation-hint"><?= $e($content['texts']['motivation']); ?></textarea>
            <div class="form-text" id="motivation-hint">Why this research exists. Opens the public page, right after the title. Blank lines start new paragraphs.</div>
          </div>
          <div>
            <label class="form-label" for="acknowledgements">Acknowledgements</label>
            <textarea class="form-control" id="acknowledgements" name="acknowledgements" rows="6" aria-describedby="acknowledgements-hint"><?= $e($content['texts']['acknowledgements']); ?></textarea>
            <div class="form-text" id="acknowledgements-hint">Closes the public page, after funding. Blank lines start new paragraphs.</div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Save texts</button>
        </div>
      </form>

    <?php } else {
      $s = SiteContentDAO::section($tab);
      $rows = $content[$tab];
      $images = $s['images'] ?? [];
      $first_image = array_key_first($images);
    ?>
      <div class="row g-4">
        <!-- Items -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h5 class="mb-0"><?= $e($s['label']); ?></h5>
              <?php if ($editing) { ?>
                <a class="btn btn-sm btn-primary" href="?tab=<?= $tab; ?>"><i class="bx bx-plus me-1" aria-hidden="true"></i>Add <?= $e($s['item']); ?></a>
              <?php } ?>
            </div>
            <?php if (!$rows) { ?>
              <p class="settings-empty mb-0">No <?= $e(strtolower($s['label'])); ?> yet. Add the first one with the form.</p>
            <?php } else { ?>
              <?php
              // Grouped sections list (and reorder) each group on its own
              $gcol = $s['group']['col'] ?? null;
              $gpos = [];
              foreach ($rows as $r) {
                $gpos[$gcol ? $r[$gcol] : ''][] = (int) $r[$s['pk']];
              }
              $gname = $gcol ? $s['fields'][$gcol]['options'] : [];
              $prev = null;
              ?>
              <ol class="settings-list">
                <?php foreach ($rows as $row) {
                  $id = (int) $row[$s['pk']];
                  [$title, $meta] = $summary($tab, $row);
                  $thumb = $first_image ? $img($row[$first_image]) : null;
                  $g = $gcol ? $row[$gcol] : '';
                  $members = $gpos[$g];
                  $i = array_search($id, $members, true);
                  if ($gcol && $g !== $prev) { $prev = $g; ?>
                    <li class="settings-group-row" aria-hidden="true"><?= $e($gname[$g] ?? $g); ?></li>
                  <?php }
                ?>
                  <li class="<?= $editing && (int) $editing[$s['pk']] === $id ? 'is-editing' : ''; ?>">
                    <?php if ($first_image) { ?>
                      <span class="settings-thumb<?= $tab === 'people' ? ' is-photo' : ''; ?>">
                        <?php if ($thumb) { ?><img src="<?= $e($thumb); ?>" alt=""><?php } else { ?><i class="bx <?= $tab === 'people' ? 'bx-user' : 'bx-image'; ?>" aria-hidden="true"></i><?php } ?>
                      </span>
                    <?php } else { ?>
                      <span class="text-muted" style="font-variant-numeric: tabular-nums; min-width: 1.5rem;"><?= $i + 1; ?></span>
                    <?php } ?>
                    <span style="min-width: 0;">
                      <span class="title"><?= $e($title); ?></span>
                      <?php if ($meta !== '') { ?><span class="meta"><?= $e($meta); ?></span><?php } ?>
                    </span>
                    <span class="settings-actions">
                      <?php foreach (['up' => 'bx-chevron-up', 'down' => 'bx-chevron-down'] as $op => $icon) {
                        $disabled = ($op === 'up' && $i === 0) || ($op === 'down' && $i === count($members) - 1); ?>
                        <form method="POST" action="/dashboard/administrator/settings/save">
                          <input type="hidden" name="csrf" value="<?= $e($csrf); ?>">
                          <input type="hidden" name="tab" value="<?= $tab; ?>">
                          <input type="hidden" name="op" value="<?= $op; ?>">
                          <input type="hidden" name="id" value="<?= $id; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-secondary" title="Move <?= $op; ?>" aria-label="Move <?= $e($title); ?> <?= $op; ?>"<?= $disabled ? ' disabled' : ''; ?>><i class="bx <?= $icon; ?>" aria-hidden="true"></i></button>
                        </form>
                      <?php } ?>
                      <a class="btn btn-sm btn-outline-secondary" href="?tab=<?= $tab; ?>&edit=<?= $id; ?>" aria-label="Edit <?= $e($title); ?>"><i class="bx bx-edit-alt" aria-hidden="true"></i></a>
                      <form method="POST" action="/dashboard/administrator/settings/save" onsubmit="return confirm('Delete “' + <?= $e(json_encode($title)); ?> + '”? This cannot be undone.');">
                        <input type="hidden" name="csrf" value="<?= $e($csrf); ?>">
                        <input type="hidden" name="tab" value="<?= $tab; ?>">
                        <input type="hidden" name="op" value="delete">
                        <input type="hidden" name="id" value="<?= $id; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Delete <?= $e($title); ?>"><i class="bx bx-trash" aria-hidden="true"></i></button>
                      </form>
                    </span>
                  </li>
                <?php } ?>
              </ol>
            <?php } ?>
          </div>
        </div>

        <!-- Add / edit form -->
        <div class="col-lg-5">
          <form class="card position-sticky settings-form" method="POST" action="/dashboard/administrator/settings/save" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= $e($csrf); ?>">
            <input type="hidden" name="tab" value="<?= $tab; ?>">
            <input type="hidden" name="op" value="save">
            <?php if ($editing) { ?><input type="hidden" name="id" value="<?= (int) $editing[$s['pk']]; ?>"><?php } ?>
            <div class="card-header">
              <h5 class="mb-0"><?= $editing ? 'Edit ' . $e($s['item']) : 'Add ' . $e($s['item']); ?></h5>
            </div>
            <div class="card-body">
              <?php
              $group = null;
              foreach ($images as $col => $label) {
                $current = $editing[$col] ?? null; ?>
                <div class="mb-3">
                  <span class="form-label d-block"><?= $e($label); ?></span>
                  <div class="settings-image">
                    <span class="settings-thumb<?= $tab === 'people' ? ' is-photo' : ''; ?>">
                      <?php if ($current) { ?><img src="<?= $e($img($current)); ?>" alt=""><?php } else { ?><i class="bx <?= $tab === 'people' ? 'bx-user' : 'bx-image'; ?>" aria-hidden="true"></i><?php } ?>
                    </span>
                    <div class="flex-grow-1">
                      <input class="form-control" type="file" id="img_<?= $col; ?>" name="img_<?= $col; ?>" accept="image/png, image/jpeg, image/webp" aria-label="<?= $e($label); ?> file">
                      <div class="form-text">PNG, JPG or WEBP, up to 2 MB.<?= $tab === 'people' ? ' A square photo works best.' : ' A transparent PNG works best.'; ?></div>
                      <?php if ($current) { ?>
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="checkbox" id="remove_<?= $col; ?>" name="remove_<?= $col; ?>" value="1">
                          <label class="form-check-label" for="remove_<?= $col; ?>">Remove current <?= $e(strtolower($label)); ?></label>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              <?php }

              foreach ($s['fields'] as $col => $f) {
                if (!empty($f['group']) && $f['group'] !== $group) {
                  $group = $f['group']; ?>
                  <p class="settings-group"><?= $e($group); ?></p>
                <?php }
                $value = $editing[$col] ?? '';
                $req = !empty($f['required']);
                $label = $e($f['label']) . ($req ? ' <span aria-hidden="true">*</span>' : '');
                $hintId = !empty($f['hint']) ? $col . '-hint' : null; ?>
                <div class="mb-3">
                  <label class="form-label" for="<?= $col; ?>"><?= $label; ?></label>
                  <?php if ($f['type'] === 'select') { ?>
                    <select class="form-select" id="<?= $col; ?>" name="<?= $col; ?>"<?= $req ? ' required' : ''; ?>>
                      <?php foreach ($f['options'] as $ov => $ol) { ?>
                        <option value="<?= $e($ov); ?>"<?= (string) $value === (string) $ov ? ' selected' : ''; ?>><?= $e($ol); ?></option>
                      <?php } ?>
                    </select>
                  <?php } elseif ($f['type'] === 'entity') { ?>
                    <select class="form-select" id="<?= $col; ?>" name="<?= $col; ?>"<?= $hintId ? ' aria-describedby="' . $hintId . '"' : ''; ?>>
                      <option value="">No entity</option>
                      <?php foreach ($content['institutions'] as $ins) { ?>
                        <option value="<?= (int) $ins['ins_id']; ?>"<?= (int) $value === (int) $ins['ins_id'] ? ' selected' : ''; ?>><?= $e($ins['ins_name'] . ($ins['ins_acronym'] ? ' (' . $ins['ins_acronym'] . ')' : '')); ?></option>
                      <?php } ?>
                    </select>
                  <?php } elseif ($f['type'] === 'textarea') { ?>
                    <textarea class="form-control" id="<?= $col; ?>" name="<?= $col; ?>" rows="4"<?= $req ? ' required' : ''; ?>><?= $e($value); ?></textarea>
                  <?php } else {
                    $type = ['url' => 'url', 'email' => 'email', 'year' => 'text'][$f['type']] ?? 'text'; ?>
                    <input class="form-control<?= $f['type'] === 'year' ? ' empp-num' : ''; ?>" type="<?= $type; ?>" id="<?= $col; ?>" name="<?= $col; ?>"
                           value="<?= $e($value); ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : ''; ?>
                           <?= isset($f['placeholder']) ? ' placeholder="' . $e($f['placeholder']) . '"' : ''; ?>
                           <?= $f['type'] === 'year' ? ' inputmode="numeric" pattern="\d{4}" maxlength="4"' : ''; ?>
                           <?= $hintId ? ' aria-describedby="' . $hintId . '"' : ''; ?><?= $req ? ' required' : ''; ?>>
                  <?php } ?>
                  <?php if ($hintId) { ?><div class="form-text" id="<?= $hintId; ?>"><?= $e($f['hint']); ?></div><?php } ?>
                </div>
              <?php } ?>
              <p class="form-text mb-0">* Required</p>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
              <?php if ($editing) { ?>
                <a class="btn btn-outline-secondary" href="?tab=<?= $tab; ?>">Cancel</a>
              <?php } else { ?><span></span><?php } ?>
              <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add ' . $e($s['item']); ?></button>
            </div>
          </form>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
