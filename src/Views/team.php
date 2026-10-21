<?php
/** @var array|null $season
 * @var array|null $group
 * @var array $targetTeam
 * @var array[] $games
 * @var array[] $teamsById
 * @var array[] $seasonsById keyed by season_id, for labeling games from any season this team played
 * @var array[] $groupsById keyed by group_id, for labeling games from any season this team played
 * @var bool $showAllSeasons
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$pageTitle = $targetTeam['name'];
$teamId = (int) $targetTeam['id'];
$returnTo = $showAllSeasons ? url('/team/' . $teamId) . '?season=all' : url('/team/' . $teamId);
?>
<div class="team-header card">
  <?= render_partial('partials/team-avatar', ['team' => $targetTeam, 'size' => 'lg', 'linked' => false]) ?>
  <div>
    <?php if ($showAllSeasons): ?>
      <p class="eyebrow"><?= h(t('Alle Saisons')) ?></p>
    <?php elseif ($season !== null && $group !== null): ?>
      <p class="eyebrow"><a href="<?= h(url('/group/' . $group['id'])) ?>"><?= h(t('Gruppe %s', $group['name'])) ?></a> &middot; <?= h($season['label']) ?></p>
    <?php endif; ?>
    <h1><?= h($targetTeam['name']) ?></h1>
    <?php $subtitle = team_players_subtitle($targetTeam); ?>
    <?php if ($subtitle !== null): ?><p class="muted"><?= h($subtitle) ?></p><?php endif; ?>
    <?php if ($showAllSeasons): ?>
      <a class="card-link" href="<?= h(url('/team/' . $teamId)) ?>">← <?= h(t('Nur aktuelle Saison anzeigen')) ?></a>
    <?php else: ?>
      <a class="card-link" href="<?= h(url('/team/' . $teamId)) ?>?season=all"><?= h(t('Alle Saisons anzeigen')) ?> →</a>
    <?php endif; ?>
    <?php if (TeamController::canEditPhoto($teamId)): ?>
      <details class="team-photo-edit">
        <summary><?= h(team_photo_url($targetTeam) !== null ? t('Foto ändern') : t('Foto hinzufügen')) ?></summary>
        <form method="post" action="<?= h(url('/team/' . $teamId . '/photo')) ?>" enctype="multipart/form-data" class="stack-form">
          <?= csrf_field() ?>
          <?php if (isset($_GET['season'])): ?><input type="hidden" name="season" value="<?= h((string) $_GET['season']) ?>"><?php endif; ?>
          <label><?= h(t('Foto (wird quadratisch zugeschnitten)')) ?> <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required></label>
          <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Foto hochladen')) ?></button>
        </form>
      </details>
    <?php endif; ?>
  </div>
</div>

<section class="fixtures">
  <h2><?= h(t('Spiele')) ?></h2>
  <?php if (empty($games)): ?>
    <p class="empty-state"><?= h(t('Noch keine Spiele.')) ?></p>
  <?php else: ?>
    <ol class="fixture-list">
      <?php $lastGroupKey = null; ?>
      <?php foreach ($games as $game): ?>
        <?php
          $gameSeason = $seasonsById[$game['season_id']] ?? null;
          $gameGroup = $game['group_id'] !== null ? ($groupsById[$game['group_id']] ?? null) : null;
          $groupKey = $game['season_id'] . '-' . $game['phase'];
        ?>
        <?php if ($groupKey !== $lastGroupKey): ?>
          <?php $lastGroupKey = $groupKey; ?>
          <li class="fixture-phase-label">
            <?= h(phase_label($game['phase'])) ?><?php if ($gameSeason !== null): ?> &middot; <?= h($gameSeason['label']) ?><?php if ($gameGroup !== null): ?> (<?= h(t('Gruppe %s', $gameGroup['name'])) ?>)<?php endif; ?><?php endif; ?>
          </li>
        <?php endif; ?>
        <?php
          $isTeamA = (int) $game['team_a_id'] === (int) $targetTeam['id'];
          $opponentId = $isTeamA ? $game['team_b_id'] : $game['team_a_id'];
          $opponent = $opponentId !== null ? ($teamsById[$opponentId] ?? null) : null;
          $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
          $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
          $viewerTeamId = $viewerTeam['id'] ?? null;
          $canEdit = $gameSeason !== null && Season::isEditable($gameSeason) && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
        ?>
        <?php $dateLabel = format_datetime_ch($game['played_date'] ?? null, $game['game_time'] ?? null); ?>
        <li class="fixture-card team-fixture-card">
          <div class="fixture-teams">
            <?= render_partial('partials/team-dot', ['team' => $targetTeam, 'seasonId' => (int) $game['season_id']]) ?>
            <span class="vs"><?= h(t('vs.')) ?></span>
            <?php if ($opponent): ?>
              <?= render_partial('partials/team-dot', ['team' => $opponent, 'seasonId' => (int) $game['season_id']]) ?>
            <?php else: ?>
              <span class="result-pending"><?= h(t('noch offen')) ?></span>
            <?php endif; ?>
          </div>
          <div class="fixture-result">
            <span class="fixture-date"><?= h($dateLabel) ?></span>
            <?= render_partial('partials/result-form', [
                'game' => $game,
                'teamA' => $teamA,
                'teamB' => $teamB,
                'returnTo' => $returnTo,
                'viewerTeamId' => $viewerTeamId,
                'canEdit' => $canEdit,
                'targetTeamId' => (int) $targetTeam['id'],
                'hideDate' => true,
                'seasonYear' => $gameSeason !== null ? (int) $gameSeason['year'] : null,
            ]) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>
