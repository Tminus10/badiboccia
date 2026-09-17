<?php
/** @var array $season
 * @var array[] $groupData
 * @var int $qualifiers
 * @var array $phases
 * @var array{champion: array|null, runnerUp: array|null, third: array|null}|null $podium
 * @var array[] $teamsById
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = 'Turnierbaum – ' . $season['label'];
$returnTo = $isCurrent ? '/bracket' : '/bracket/' . $season['id'];

$renderGame = function (array $game) use ($teamsById, $returnTo, $viewerTeam, $admin, $isCurrent, $season): void {
    $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
    $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
    $viewerTeamId = $viewerTeam['id'] ?? null;
    $canEdit = $isCurrent && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
    $winnerId = Game::winnerTeamId($game);
    ?>
    <div class="bracket-team<?= $teamA !== null && $winnerId === (int) $teamA['id'] ? ' winner' : '' ?>"><?= render_bracket_slot($teamA, (int) $season['id']) ?></div>
    <div class="bracket-team<?= $teamB !== null && $winnerId === (int) $teamB['id'] ? ' winner' : '' ?>"><?= render_bracket_slot($teamB, (int) $season['id']) ?></div>
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
    <?php
};

$sfPair = $phases['sf'];
$finalGame = $phases['final'][0] ?? null;
$thirdGame = $phases['third'][0] ?? null;
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><?= h($season['label']) ?></p>
    <h1>Turnierbaum</h1>
  </div>
  <a class="btn btn-secondary" href="<?= h(url($isCurrent ? '/' : '/season/' . $season['id'])) ?>">Übersicht ansehen →</a>
</div>

<?php if ($podium !== null): ?>
  <section class="card podium-card">
    <h2>Rangliste</h2>
    <ul class="podium-list">
      <li class="podium-row podium-1">
        <span class="podium-medal" aria-hidden="true">🥇</span>
        <span class="podium-meta">
          <span class="podium-place">1. Platz</span>
          <?= render_partial('partials/team-dot', ['team' => $podium['champion'], 'seasonId' => (int) $season['id']]) ?>
        </span>
      </li>
      <li class="podium-row podium-2">
        <span class="podium-medal" aria-hidden="true">🥈</span>
        <span class="podium-meta">
          <span class="podium-place">2. Platz</span>
          <?= render_partial('partials/team-dot', ['team' => $podium['runnerUp'], 'seasonId' => (int) $season['id']]) ?>
        </span>
      </li>
      <li class="podium-row podium-3">
        <span class="podium-medal" aria-hidden="true">🥉</span>
        <span class="podium-meta">
          <span class="podium-place">3. Platz</span>
          <?php if ($podium['third'] !== null): ?>
            <?= render_partial('partials/team-dot', ['team' => $podium['third'], 'seasonId' => (int) $season['id']]) ?>
          <?php else: ?>
            <span class="muted">Spiel um Platz 3 steht noch aus</span>
          <?php endif; ?>
        </span>
      </li>
    </ul>
  </section>
<?php endif; ?>

<div class="bracket-titles">
  <h2 class="bt-groups">Gruppenphase</h2>
  <div class="bt-tree">
    <h2 class="bt-qf">Viertelfinal</h2>
    <h2 class="bt-sf">Halbfinal</h2>
    <h2 class="bt-final">🏆 Final</h2>
  </div>
</div>

<div class="bracket">
  <div class="bracket-col bracket-col-groups">
    <h2 class="bracket-col-title-mobile">Gruppenphase</h2>
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
                <tr class="<?= ($row['rank'] <= $qualifiers && $entry['anyPlayed']) ? 'qualifies' : '' ?>">
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

  <div class="bracket-tree">
    <div class="bracket-col bracket-col-qf">
      <h2 class="bracket-col-title-mobile">Viertelfinal</h2>
      <?php foreach ($phases['qf'] as $i => $game): ?>
        <div class="bracket-game bracket-qf-row-<?= $i + 1 ?>"><?php $renderGame($game); ?></div>
      <?php endforeach; ?>
    </div>

    <div class="bracket-wire bracket-wire-a"></div>
    <div class="bracket-wire bracket-wire-b"></div>

    <div class="bracket-col bracket-col-sf">
      <h2 class="bracket-col-title-mobile">Halbfinal</h2>
      <?php foreach ($sfPair as $i => $game): ?>
        <div class="bracket-game bracket-sf-row-<?= $i + 1 ?>"><?php $renderGame($game); ?></div>
      <?php endforeach; ?>
    </div>

    <div class="bracket-wire bracket-wire-c1"></div>
    <div class="bracket-wire bracket-wire-c2"></div>
    <div class="bracket-wire bracket-wire-c-stub"></div>

    <div class="bracket-col bracket-col-final">
      <h2 class="bracket-col-title-mobile">🏆 Final</h2>
      <?php if ($finalGame !== null): ?>
        <div class="bracket-game bracket-game-final"><?php $renderGame($finalGame); ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($thirdGame !== null): ?>
  <div class="bracket-third-row">
    <div class="bracket-third-col">
      <h2>Spiel um Platz 3</h2>
      <p class="bracket-third-hint">Verlierer der Halbfinals</p>
      <div class="bracket-game bracket-game-third"><?php $renderGame($thirdGame); ?></div>
    </div>
  </div>
<?php endif; ?>
