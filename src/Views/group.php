<?php
/** @var array $season
 * @var array $group
 * @var array[] $standings
 * @var array[] $games
 * @var array[] $teamsById
 * @var int $qualifiers
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$qualifierWords = [2 => 'zwei', 3 => 'drei'];
$qualifierWord = $qualifierWords[$qualifiers] ?? (string) $qualifiers;
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = 'Gruppe ' . $group['name'];
$returnTo = '/group/' . $group['id'];
$anyPlayed = false;
foreach ($games as $g) {
    if (Game::isComplete($g)) {
        $anyPlayed = true;
        break;
    }
}
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url($isCurrent ? '/' : '/season/' . $season['id'])) ?>"><?= h($season['label']) ?></a></p>
    <h1>Gruppe <?= h($group['name']) ?></h1>
  </div>
</div>

<?php if (empty($standings)): ?>
  <p class="empty-state">Für diese Gruppe sind noch keine Teams angelegt.</p>
<?php else: ?>
  <section class="card">
    <table class="standings-table">
      <thead>
        <tr>
          <th class="rank-col">#</th>
          <th>Team</th>
          <th>Sp</th>
          <th>S</th>
          <th>N</th>
          <th>U</th>
          <th>Sätze</th>
          <th>Pkt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($standings as $row): ?>
          <tr class="<?= ($row['rank'] <= $qualifiers && $anyPlayed) ? 'qualifies' : '' ?>">
            <td class="rank-col"><?= (int) $row['rank'] ?></td>
            <td><?= render_partial('partials/team-dot', ['team' => $row['team'], 'seasonId' => (int) $season['id']]) ?></td>
            <td><?= (int) $row['played'] ?></td>
            <td><?= (int) $row['won'] ?></td>
            <td><?= (int) $row['lost'] ?></td>
            <td><?= (int) $row['tied'] ?></td>
            <td><?= (int) $row['sets_for'] ?>:<?= (int) $row['sets_against'] ?></td>
            <td class="pts"><?= (int) $row['points'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="hint">Die ersten <?= h($qualifierWord) ?> Teams (hervorgehoben) kommen für das Viertelfinale in Frage.</p>
  </section>
<?php endif; ?>

<section class="fixtures">
  <h2>Spiele</h2>
  <?php if (empty($games)): ?>
    <p class="empty-state">Noch keine Spiele erstellt.</p>
  <?php else: ?>
    <ol class="fixture-list">
      <?php foreach ($games as $i => $game): ?>
        <?php
          $teamA = $game['team_a_id'] !== null ? ($teamsById[$game['team_a_id']] ?? null) : null;
          $teamB = $game['team_b_id'] !== null ? ($teamsById[$game['team_b_id']] ?? null) : null;
          $viewerTeamId = $viewerTeam['id'] ?? null;
          $canEdit = $isCurrent && ($admin !== null || ($viewerTeamId !== null && Game::isParticipant($game, (int) $viewerTeamId)));
        ?>
        <li class="fixture-card">
          <div class="fixture-teams">
            <span class="fixture-num">Spiel <?= $i + 1 ?></span>
            <?php if ($teamA): ?><?= render_partial('partials/team-dot', ['team' => $teamA, 'seasonId' => (int) $season['id']]) ?><?php endif; ?>
            <span class="vs">–</span>
            <?php if ($teamB): ?><?= render_partial('partials/team-dot', ['team' => $teamB, 'seasonId' => (int) $season['id']]) ?><?php endif; ?>
          </div>
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
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>
