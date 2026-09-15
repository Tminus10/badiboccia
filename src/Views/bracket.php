<?php
/** @var array $season
 * @var array[] $groupData
 * @var array $phases
 * @var array[] $teamsById
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = 'Turnierbaum – ' . $season['label'];
$returnTo = $isCurrent ? '/bracket' : '/bracket/' . $season['id'];
$phaseLabels = ['qf' => 'Viertelfinal', 'sf' => 'Halbfinal', 'final' => 'Final', 'third' => 'Spiel um Platz 3'];
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><?= h($season['label']) ?></p>
    <h1>Turnierbaum</h1>
  </div>
  <a class="btn btn-secondary" href="<?= h(url($isCurrent ? '/' : '/season/' . $season['id'])) ?>">Übersicht ansehen →</a>
</div>

<div class="bracket">
  <div class="bracket-col bracket-col-groups">
    <h2>Gruppenphase</h2>
    <?php foreach ($groupData as $entry): ?>
      <?php $group = $entry['group']; ?>
      <div class="bracket-group-card">
        <a class="bracket-group-title" href="<?= h(url('/group/' . $group['id'])) ?>">Gruppe <?= h($group['name']) ?></a>
        <?php if (empty($entry['standings'])): ?>
          <p class="empty-state small">Noch keine Teams.</p>
        <?php else: ?>
          <table class="standings-table standings-compact">
            <tbody>
              <?php foreach ($entry['standings'] as $row): ?>
                <tr class="<?= ($row['rank'] <= 2 && $entry['anyPlayed']) ? 'qualifies' : '' ?>">
                  <td class="rank-col"><?= (int) $row['rank'] ?></td>
                  <td><?= render_partial('partials/team-dot', ['team' => $row['team'], 'seasonId' => (int) $season['id']]) ?></td>
                  <td class="pts"><?= (int) $row['points'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php foreach (['qf', 'sf', 'final', 'third'] as $phase): ?>
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
          <div class="bracket-team"><?= render_bracket_slot($teamA, (int) $season['id']) ?></div>
          <div class="bracket-team"><?= render_bracket_slot($teamB, (int) $season['id']) ?></div>
          <div class="fixture-result">
            <?= render_partial('partials/result-form', [
                'game' => $game,
                'teamA' => $teamA,
                'teamB' => $teamB,
                'returnTo' => $returnTo,
                'viewerTeamId' => $viewerTeamId,
                'canEdit' => $canEdit,
                'seasonYear' => (int) $season['year'],
            ]) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>
