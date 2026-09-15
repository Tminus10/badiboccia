<?php
/** @var array $admin
 * @var array[] $seasons
 * @var array[] $admins
 */
$pageTitle = 'Admin';
?>
<div class="page-head"><div><h1>Admin-Bereich</h1><p class="muted">Angemeldet als <?= h($admin['display_name']) ?></p></div></div>

<section class="card">
  <h2>Teams</h2>
  <p class="muted">Alle Teams über alle Saisons hinweg verwalten: Namen, Spieler, Fotos, PINs.</p>
  <a class="btn btn-secondary" href="<?= h(url('/admin/teams')) ?>">Teams verwalten</a>
</section>

<section class="card">
  <h2>Saisons</h2>
  <?php if (empty($seasons)): ?>
    <p class="empty-state small">Noch keine Saison angelegt.</p>
  <?php else: ?>
    <ul class="admin-list">
      <?php foreach ($seasons as $s): ?>
        <li>
          <a href="<?= h(url('/admin/season/' . $s['id'])) ?>"><strong><?= h($s['label']) ?></strong></a>
          <?php if ((int) $s['is_current'] === 1): ?>
            <span class="badge badge-current">Aktiv</span>
            <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/deactivate')) ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-ghost btn-sm" type="submit">Deaktivieren</button>
            </form>
          <?php else: ?>
            <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/activate')) ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-ghost btn-sm" type="submit">Als aktiv setzen</button>
            </form>
          <?php endif; ?>
          <form method="post" action="<?= h(url('/admin/season/' . $s['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Saison \'<?= h(addslashes($s['label'])) ?>\' wirklich unwiderruflich löschen? Alle Gruppen, Spiele und Resultate dieser Saison gehen dabei verloren.');">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit">Löschen</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <details class="add-form">
    <summary>+ Neue Saison anlegen</summary>
    <form method="post" action="<?= h(url('/admin/season')) ?>" class="stack-form">
      <?= csrf_field() ?>
      <label>Bezeichnung
        <input type="text" name="label" placeholder="Sommerturnier <?= date('Y') ?>" required>
      </label>
      <label>Jahr
        <input type="number" name="year" value="<?= date('Y') ?>" required>
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="make_current" value="1" checked>
        Direkt als aktive Saison setzen
      </label>
      <button class="btn btn-primary" type="submit">Saison erstellen</button>
    </form>
  </details>
</section>

<section class="card">
  <h2>Admin-Accounts</h2>
  <ul class="admin-list">
    <?php foreach ($admins as $a): ?>
      <li>
        <span><?= h($a['display_name']) ?> <span class="muted">(<?= h($a['username']) ?>)</span></span>
        <?php if ((int) $a['id'] !== (int) $admin['id']): ?>
          <form method="post" action="<?= h(url('/admin/admins/' . $a['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Admin \'<?= h(addslashes($a['display_name'])) ?>\' wirklich löschen?');">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit">Entfernen</button>
          </form>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <details class="add-form">
    <summary>+ Neuen Admin anlegen</summary>
    <form method="post" action="<?= h(url('/admin/admins')) ?>" class="stack-form">
      <?= csrf_field() ?>
      <label>Anzeigename
        <input type="text" name="display_name" required>
      </label>
      <label>Benutzername
        <input type="text" name="username" required autocomplete="off">
      </label>
      <label>Passwort
        <input type="password" name="password" minlength="6" required autocomplete="new-password">
      </label>
      <button class="btn btn-primary" type="submit">Admin erstellen</button>
    </form>
  </details>
</section>
