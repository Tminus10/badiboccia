<?php
/** @var array $season
 * @var array $group
 * @var array $targetTeam
 * @var array[] $games
 * @var array[] $teamsById
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = $targetTeam['name'];
$returnTo = '/team/' . $targetTeam['id'];
$phaseLabels = ['group' => 'Gruppenphase', 'qf' => 'Viertelfinal', 'sf' => 'Halbfinal', 'final' => 'Final'];
?>
<div class="team-header card">
  <?= render_partial('partials/team-avatar', ['team' => $targetTeam, 'size' => 'lg', 'linked' => false]) ?>
  <div>
    <p class="eyebrow"><a href="<?= h(url('/group/' . $group['id'])) ?>">Gruppe <?= h($group['name']) ?></a> &middot; <?= h($season['label']) ?></p>
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
      <?php $lastPhase = null; ?>
      <?php foreach ($games as $game): ?>
        <?php if ($game['phase'] !== $lastPhase): ?>
          <?php $lastPhase = $game['phase']; ?>
          <li class="fixture-phase-label"><?= h($phaseLabels[$game['phase']] ?? $game['phase']) ?></li>
        <?php endif; ?>
        <?php
          $isTeamA = (int) $game['team_a_id'] === (int) $targetTeam['id'];
          $opponentId = $isTeamA ? $game['team_b_id'] : $game['team_a_id'];
          $opponent = $opponentId !== null ? ($teamsById[$opponentId] ?? null) : null;
          $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
          $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
          $viewerTeamId = $viewerTeam['id'] ?? null;
          $canEdit = $isCurrent && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
        ?>
        <li class="fixture-card">
          <div class="fixture-teams">
            <span class="vs-label">gegen</span>
            <?php if ($opponent): ?>
              <?= render_partial('partials/team-dot', ['team' => $opponent]) ?>
            <?php else: ?>
              <span class="result-pending">noch offen</span>
            <?php endif; ?>
          </div>
          <div class="fixture-result">
            <?= render_partial('partials/result-form', [
                'game' => $game,
                'teamA' => $teamA,
                'teamB' => $teamB,
                'returnTo' => $returnTo,
                'viewerTeamId' => $viewerTeamId,
                'canEdit' => $canEdit,
            ]) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>
