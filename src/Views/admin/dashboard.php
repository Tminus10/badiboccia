<?php
/** @var array $admin
 * @var array[] $seasons
 * @var array[] $admins
 */
$pageTitle = t('Admin');
?>
<div class="page-head"><div><h1><?= h(t('Admin-Bereich')) ?></h1><p class="muted"><?= h(t('Angemeldet als %s', $admin['display_name'])) ?></p></div></div>

<section class="card">
  <h2><?= h(t('Teams')) ?></h2>
  <p class="muted"><?= h(t('Alle Teams über alle Saisons hinweg verwalten: Namen, Spieler, Fotos, PINs.')) ?></p>
  <a class="btn btn-secondary" href="<?= h(url('/admin/teams')) ?>"><?= h(t('Teams verwalten')) ?></a>
</section>

<section class="card">
  <h2><?= h(t('Saisons')) ?></h2>
  <?php if (empty($seasons)): ?>
    <p class="empty-state small"><?= h(t('Noch keine Saison angelegt.')) ?></p>
  <?php else: ?>
    <ul class="admin-list">
      <?php foreach ($seasons as $s): ?>
        <li>
          <a href="<?= h(url('/admin/season/' . $s['id'])) ?>"><strong><?= h($s['label']) ?></strong></a>
          <?php if (Season::isDemo($s)): ?>
            <span class="badge badge-demo"><?= h(t('Demo')) ?></span>
          <?php elseif ((int) $s['is_current'] === 1): ?>
            <span class="badge badge-current"><?= h(t('Aktiv')) ?></span>
            <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/deactivate')) ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('Deaktivieren')) ?></button>
            </form>
          <?php else: ?>
            <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/activate')) ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('Als aktiv setzen')) ?></button>
            </form>
          <?php endif; ?>
          <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Saison «%s» wirklich unwiderruflich löschen? Alle Gruppen, Spiele und Resultate dieser Saison gehen dabei verloren.', $s['label']))) ?>);">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Löschen')) ?></button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <details class="add-form">
    <summary>+ <?= h(t('Neue Saison anlegen')) ?></summary>
    <form method="post" action="<?= h(url('/admin/season')) ?>" class="stack-form">
      <?= csrf_field() ?>
      <label><?= h(t('Bezeichnung')) ?>
        <input type="text" name="label" placeholder="<?= h(t('Sommerturnier %d', (int) date('Y'))) ?>" required>
      </label>
      <label><?= h(t('Jahr')) ?>
        <input type="number" name="year" value="<?= date('Y') ?>" required>
      </label>
      <label><?= h(t('Anzahl Gruppen')) ?>
        <select name="group_count">
          <option value="4" selected><?= h(t('%d Gruppen', 4)) ?></option>
          <option value="3"><?= h(t('%d Gruppen', 3)) ?></option>
        </select>
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="make_current" value="1" checked>
        <?= h(t('Direkt als aktive Saison setzen')) ?>
      </label>
      <button class="btn btn-primary" type="submit"><?= h(t('Saison erstellen')) ?></button>
    </form>
  </details>
</section>

<section class="card">
  <h2><?= h(t('Admin-Accounts')) ?></h2>
  <ul class="admin-list">
    <?php foreach ($admins as $a): ?>
      <li>
        <span><?= h($a['display_name']) ?> <span class="muted">(<?= h($a['username']) ?>)</span></span>
        <details class="admin-team-edit">
          <summary><?= h(t('Bearbeiten')) ?></summary>
          <form method="post" action="<?= h(url('/admin/admins/' . $a['id'] . '/update')) ?>" class="stack-form">
            <?= csrf_field() ?>
            <label><?= h(t('Anzeigename')) ?>
              <input type="text" name="display_name" value="<?= h($a['display_name']) ?>" required>
            </label>
            <label><?= h(t('Benutzername')) ?>
              <input type="text" name="username" value="<?= h($a['username']) ?>" required autocomplete="off">
            </label>
            <label><?= h(t('Neues Passwort')) ?> <span class="muted">(<?= h(t('leer lassen, um es nicht zu ändern')) ?>)</span>
              <input type="password" name="password" minlength="6" autocomplete="new-password">
            </label>
            <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Speichern')) ?></button>
          </form>
        </details>
        <?php if ((int) $a['id'] !== (int) $admin['id']): ?>
          <form method="post" action="<?= h(url('/admin/admins/' . $a['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Admin «%s» wirklich löschen?', $a['display_name']))) ?>);">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Entfernen')) ?></button>
          </form>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <details class="add-form">
    <summary>+ <?= h(t('Neuen Admin anlegen')) ?></summary>
    <form method="post" action="<?= h(url('/admin/admins')) ?>" class="stack-form">
      <?= csrf_field() ?>
      <label><?= h(t('Anzeigename')) ?>
        <input type="text" name="display_name" placeholder="<?= h(t('z. B. %s', 'Roli')) ?>" required>
      </label>
      <label><?= h(t('Benutzername')) ?> <span class="muted">(<?= h(t('zum Anmelden, nicht der Anzeigename')) ?>)</span>
        <input type="text" name="username" placeholder="<?= h(t('z. B. %s', 'roli')) ?>" required autocomplete="off">
      </label>
      <label><?= h(t('Passwort')) ?>
        <input type="password" name="password" minlength="6" required autocomplete="new-password">
      </label>
      <button class="btn btn-primary" type="submit"><?= h(t('Admin erstellen')) ?></button>
    </form>
  </details>
</section>
