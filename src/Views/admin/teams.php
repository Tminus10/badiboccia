<?php
/** @var array $admin
 * @var array[] $teams each with a 'seasons' key: array of {season_id, group_id, season_label, year, group_name}
 * @var array[] $groups every team_group joined with its season_label/year
 */
$pageTitle = 'Teams';
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url('/admin')) ?>">Admin</a></p>
    <h1>Teams</h1>
    <p class="muted">Alle Teams über alle Saisons hinweg. Name, Foto und PIN gelten für ein Team dauerhaft.</p>
  </div>
</div>

<section class="card">
  <?php if (empty($teams)): ?>
    <p class="empty-state">Noch keine Teams. Lege zuerst eine Saison an und füge dort ein Team hinzu.</p>
  <?php else: ?>
    <ul class="admin-team-list">
      <?php foreach ($teams as $team): ?>
        <li class="admin-team-row">
          <?= render_partial('partials/team-avatar', ['team' => $team, 'size' => 'sm', 'linked' => true]) ?>
          <div class="admin-team-info">
            <strong><?= h($team['name']) ?></strong>
            <?php $subtitle = team_players_subtitle($team); ?>
            <?php if ($subtitle !== null): ?><span class="muted"><?= h($subtitle) ?></span><?php endif; ?>
            <span class="muted pin-display">PIN: <strong><?= h($team['pin']) ?></strong></span>
            <?php if (!empty($team['seasons'])): ?>
              <span class="muted">
                <?php foreach ($team['seasons'] as $i => $s): ?><?= $i > 0 ? ', ' : '' ?><a href="<?= h(url('/admin/season/' . $s['season_id'])) ?>"><?= h($s['season_label']) ?> (Gruppe <?= h($s['group_name']) ?>)</a><?php endforeach; ?>
              </span>
            <?php endif; ?>
          </div>
          <details class="admin-team-edit">
            <summary>Bearbeiten</summary>

            <form method="post" action="<?= h(url('/admin/team/' . $team['id'])) ?>" class="stack-form">
              <?= csrf_field() ?>
              <label>Teamname <input type="text" name="name" value="<?= h($team['name']) ?>" required></label>
              <label>Spieler 1 <input type="text" name="player1" value="<?= h($team['player1']) ?>" required></label>
              <label>Spieler 2 <input type="text" name="player2" value="<?= h($team['player2']) ?>" required></label>
              <button class="btn btn-primary btn-sm" type="submit">Speichern</button>
            </form>

            <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/photo')) ?>" enctype="multipart/form-data" class="stack-form">
              <?= csrf_field() ?>
              <label>Foto <input type="file" name="photo" accept="image/png,image/jpeg,image/webp"></label>
              <button class="btn btn-secondary btn-sm" type="submit">Foto hochladen</button>
            </form>

            <div class="admin-team-actions">
              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/pin')) ?>" class="inline-form" onsubmit="return confirm('Neuen PIN für <?= h(addslashes($team['name'])) ?> erstellen? Der alte PIN wird ungültig – in jeder Saison.');">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm" type="submit">PIN zurücksetzen</button>
              </form>
              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/delete-all')) ?>" class="inline-form" onsubmit="return confirm('Team <?= h(addslashes($team['name'])) ?> UNWIDERRUFLICH löschen, inklusive aller Spiele und Saisons? Das kann nicht rückgängig gemacht werden.');">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm btn-danger" type="submit">Endgültig löschen</button>
              </form>
            </div>
          </details>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if (!empty($groups)): ?>
    <details class="add-form">
      <summary>+ Neues Team erstellen</summary>
      <form method="post" action="<?= h(url('/admin/teams/create')) ?>" class="stack-form">
        <?= csrf_field() ?>
        <label>Teamname <input type="text" name="name" placeholder="z.B. Debi &amp; Erika" required></label>
        <label>Spieler 1 <input type="text" name="player1" required></label>
        <label>Spieler 2 <input type="text" name="player2" required></label>
        <label>Saison &amp; Gruppe
          <select name="group_id" required>
            <option value="">– wählen –</option>
            <?php foreach ($groups as $g): ?>
              <option value="<?= (int) $g['id'] ?>"><?= h($g['season_label']) ?> – Gruppe <?= h($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="btn btn-primary btn-sm" type="submit">Team erstellen</button>
      </form>
    </details>
  <?php endif; ?>
</section>
