<?php
/** @var array $season
 * @var array $phases
 * @var array[] $teamsById
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = 'Turnierbaum – ' . $season['label'];
$returnTo = $isCurrent ? '/bracket' : '/bracket/' . $season['id'];
$phaseLabels = ['qf' => 'Viertelfinal', 'sf' => 'Halbfinal', 'final' => 'Final'];
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url($isCurrent ? '/' : '/season/' . $season['id'])) ?>"><?= h($season['label']) ?></a></p>
    <h1>Turnierbaum</h1>
  </div>
</div>

<div class="bracket">
  <?php foreach (['qf', 'sf', 'final'] as $phase): ?>
    <div class="bracket-col bracket-col-<?= h($phase) ?>">
      <h2><?= h($phaseLabels[$phase]) ?></h2>
      <?php foreach ($phases[$phase] as $game): ?>
        <?php
          $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
          $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
          $viewerTeamId = $viewerTeam['id'] ?? null;
          $canEdit = $isCurrent && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
        ?>
        <div class="bracket-game">
          <div class="bracket-team"><?= render_bracket_slot($teamA) ?></div>
          <div class="bracket-team"><?= render_bracket_slot($teamB) ?></div>
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
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>
