<?php
/** @var array|null $season
 * @var array|null $group
 * @var array $targetTeam
 * @var array[] $games
 * @var array[] $teamsById
 * @var array[] $seasonsById keyed by season_id, for labeling games from any season this team played
 * @var array[] $groupsById keyed by group_id, for labeling games from any season this team played
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$pageTitle = $targetTeam['name'];
$returnTo = '/team/' . $targetTeam['id'];
$phaseLabels = ['group' => 'Gruppenphase', 'qf' => 'Viertelfinal', 'sf' => 'Halbfinal', 'final' => 'Final'];
?>
<div class="team-header card">
  <?= render_partial('partials/team-avatar', ['team' => $targetTeam, 'size' => 'lg', 'linked' => false]) ?>
  <div>
    <?php if ($season !== null && $group !== null): ?>
      <p class="eyebrow"><a href="<?= h(url('/group/' . $group['id'])) ?>">Gruppe <?= h($group['name']) ?></a> &middot; <?= h($season['label']) ?></p>
    <?php endif; ?>
    <h1><?= h($targetTeam['name']) ?></h1>
    <?php $subtitle = team_players_subtitle($targetTeam); ?>
    <?php if ($subtitle !== null): ?><p class="muted"><?= h($subtitle) ?></p><?php endif; ?>
  </div>
</div>

<section class="fixtures">
  <h2>Spiele</h2>
  <?php if (empty($games)): ?>
    <p class="empty-state">Noch keine Spiele.</p>
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
            <?= h($phaseLabels[$game['phase']] ?? $game['phase']) ?><?php if ($gameSeason !== null): ?> &middot; <?= h($gameSeason['label']) ?><?php if ($gameGroup !== null): ?> (Gruppe <?= h($gameGroup['name']) ?>)<?php endif; ?><?php endif; ?>
          </li>
        <?php endif; ?>
        <?php
          $isTeamA = (int) $game['team_a_id'] === (int) $targetTeam['id'];
          $opponentId = $isTeamA ? $game['team_b_id'] : $game['team_a_id'];
          $opponent = $opponentId !== null ? ($teamsById[$opponentId] ?? null) : null;
          $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
          $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
          $viewerTeamId = $viewerTeam['id'] ?? null;
          $gameIsCurrent = $gameSeason !== null && (int) $gameSeason['is_current'] === 1;
          $canEdit = $gameIsCurrent && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
        ?>
        <?php $dateLabel = !empty($game['played_date']) ? format_date_ch($game['played_date']) : ''; ?>
        <li class="fixture-card team-fixture-card">
          <div class="fixture-teams">
            <?= render_partial('partials/team-dot', ['team' => $targetTeam]) ?>
            <span class="vs">vs.</span>
            <?php if ($opponent): ?>
              <?= render_partial('partials/team-dot', ['team' => $opponent]) ?>
            <?php else: ?>
              <span class="result-pending">noch offen</span>
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
