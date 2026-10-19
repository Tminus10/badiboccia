<?php
/** @var array $admin
 * @var array[] $teams each with a 'seasons' key: array of {season_id, group_id, season_label, year, group_name}
 */
$pageTitle = t('Teams');
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url('/admin')) ?>"><?= h(t('Admin')) ?></a></p>
    <h1><?= h(t('Teams')) ?></h1>
    <p class="muted"><?= h(t('Alle Teams über alle Saisons hinweg. Name, Foto und PIN gelten für ein Team dauerhaft.')) ?></p>
  </div>
</div>

<section class="card">
  <?php if (empty($teams)): ?>
    <p class="empty-state"><?= h(t('Noch keine Teams. Erstelle unten dein erstes Team.')) ?></p>
  <?php else: ?>
    <ul class="admin-team-list">
      <?php foreach ($teams as $team): ?>
        <li class="admin-team-row">
          <?= render_partial('partials/team-avatar', ['team' => $team, 'size' => 'sm', 'linked' => true, 'seasonId' => 'all']) ?>
          <div class="admin-team-info">
            <strong><?= h($team['name']) ?></strong>
            <?php $subtitle = team_players_subtitle($team); ?>
            <?php if ($subtitle !== null): ?><span class="muted"><?= h($subtitle) ?></span><?php endif; ?>
            <span class="muted pin-display">PIN: <strong><?= h($team['pin']) ?></strong></span>
            <?php if (!empty($team['seasons'])): ?>
              <span class="muted">
                <?php foreach ($team['seasons'] as $i => $s): ?><?= $i > 0 ? ', ' : '' ?><a href="<?= h(url('/admin/season/' . $s['season_id'])) ?>"><?= h($s['season_label']) ?> (<?= h(t('Gruppe %s', $s['group_name'])) ?>)</a><?php endforeach; ?>
              </span>
            <?php else: ?>
              <span class="muted"><?= h(t('Noch keiner Saison zugeteilt')) ?></span>
            <?php endif; ?>
          </div>
          <details class="admin-team-edit">
            <summary><?= h(t('Bearbeiten')) ?></summary>

            <form method="post" action="<?= h(url('/admin/team/' . $team['id'])) ?>" class="stack-form">
              <?= csrf_field() ?>
              <label><?= h(t('Teamname')) ?> <input type="text" name="name" value="<?= h($team['name']) ?>" required></label>
              <label><?= h(t('Spieler %d', 1)) ?> <input type="text" name="player1" value="<?= h($team['player1']) ?>" required></label>
              <label><?= h(t('Spieler %d', 2)) ?> <input type="text" name="player2" value="<?= h($team['player2']) ?>" required></label>
              <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Speichern')) ?></button>
            </form>

            <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/photo')) ?>" enctype="multipart/form-data" class="stack-form">
              <?= csrf_field() ?>
              <label><?= h(t('Foto')) ?> <input type="file" name="photo" accept="image/png,image/jpeg,image/webp"></label>
              <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Foto hochladen')) ?></button>
            </form>

            <div class="admin-team-actions">
              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/pin')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Neuen PIN für %s erstellen? Der alte PIN wird ungültig – in jeder Saison.', $team['name']))) ?>);">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('PIN zurücksetzen')) ?></button>
              </form>
              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/delete-all')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Team %s UNWIDERRUFLICH löschen, inklusive aller Spiele und Saisons? Das kann nicht rückgängig gemacht werden.', $team['name']))) ?>);">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Endgültig löschen')) ?></button>
              </form>
            </div>
          </details>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <details class="add-form">
    <summary>+ <?= h(t('Neues Team erstellen')) ?></summary>
    <form method="post" action="<?= h(url('/admin/teams/create')) ?>" class="stack-form">
      <?= csrf_field() ?>
      <label><?= h(t('Teamname')) ?> <input type="text" name="name" placeholder="<?= h(t('z. B. %s', 'Debi & Erika')) ?>" required></label>
      <label><?= h(t('Spieler %d', 1)) ?> <input type="text" name="player1" required></label>
      <label><?= h(t('Spieler %d', 2)) ?> <input type="text" name="player2" required></label>
      <p class="muted"><?= h(t('Weise das Team danach über eine Saison einer Gruppe zu.')) ?></p>
      <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Team erstellen')) ?></button>
    </form>
  </details>
</section>
