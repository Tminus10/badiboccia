<?php
/** @var array $game
 * @var array|null $teamA
 * @var array|null $teamB
 * @var string $returnTo
 * @var int|null $viewerTeamId
 * @var bool $canEdit
 */
$complete = Game::isComplete($game);
$viewerIsA = $viewerTeamId !== null && $teamA !== null && (int) $game['team_a_id'] === $viewerTeamId;
$viewerIsB = $viewerTeamId !== null && $teamB !== null && (int) $game['team_b_id'] === $viewerTeamId;
$today = date('Y-m-d');
$defaultDate = $game['played_date'] ?? $today;

if ($teamA === null || $teamB === null) {
    $options = [];
} elseif ($viewerIsA) {
    $options = [
        ['2-0', 'Sieg 2:0'],
        ['2-1', 'Sieg 2:1'],
        ['1-2', 'Niederlage 1:2'],
        ['0-2', 'Niederlage 0:2'],
    ];
} elseif ($viewerIsB) {
    $options = [
        ['0-2', 'Sieg 2:0'],
        ['1-2', 'Sieg 2:1'],
        ['2-1', 'Niederlage 1:2'],
        ['2-0', 'Niederlage 0:2'],
    ];
} else {
    $options = [
        ['2-0', $teamA['name'] . ' gewinnt 2:0'],
        ['2-1', $teamA['name'] . ' gewinnt 2:1'],
        ['1-2', $teamB['name'] . ' gewinnt 2:1'],
        ['0-2', $teamB['name'] . ' gewinnt 2:0'],
    ];
}
?>
<?php if ($complete): ?>
  <div class="result-chip">
    <span class="score"><?= h(format_result($game)) ?></span>
    <?php if (!empty($game['played_date'])): ?><span class="played-date"><?= h(format_date_ch($game['played_date'])) ?></span><?php endif; ?>
  </div>
<?php elseif ($teamA === null || $teamB === null): ?>
  <span class="result-pending">Gegner steht noch nicht fest</span>
<?php endif; ?>

<?php if ($canEdit && $options): ?>
  <details class="result-entry">
    <summary><?= $complete ? 'Resultat korrigieren' : 'Resultat eintragen' ?></summary>
    <form method="post" action="<?= h(url('/game/' . $game['id'] . '/result')) ?>" class="score-form">
      <?= csrf_field() ?>
      <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
      <label class="date-field">
        <span>Datum</span>
        <input type="date" name="played_date" value="<?= h($defaultDate) ?>" max="<?= h($today) ?>" required>
      </label>
      <div class="score-buttons">
        <?php foreach ($options as [$value, $label]): ?>
          <button type="submit" name="score" value="<?= h($value) ?>" class="score-btn"><?= h($label) ?></button>
        <?php endforeach; ?>
      </div>
    </form>
  </details>
<?php endif; ?>
