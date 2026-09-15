<?php
/** @var array $game
 * @var array|null $teamA
 * @var array|null $teamB
 * @var string $returnTo
 * @var int|null $viewerTeamId
 * @var bool $canEdit
 * @var int|null $targetTeamId whose page this is being shown on, for score orientation (null = absolute team A:B score)
 * @var bool|null $hideDate omit the date from the chip (caller renders it separately)
 * @var int|null $seasonYear the season's year, used to default the date picker to the right year for past seasons
 */
$targetTeamId = $targetTeamId ?? null;
$hideDate = $hideDate ?? false;
$complete = Game::isComplete($game);
$viewerIsA = $viewerTeamId !== null && $teamA !== null && (int) $game['team_a_id'] === $viewerTeamId;
$viewerIsB = $viewerTeamId !== null && $teamB !== null && (int) $game['team_b_id'] === $viewerTeamId;
$today = date('Y-m-d');
$seasonDefaultDate = sprintf('%04d-07-15', $seasonYear ?? (int) date('Y'));
if ($seasonDefaultDate > $today) {
    $seasonDefaultDate = $today;
}
$defaultDate = $game['played_date'] ?? $seasonDefaultDate;
$currentValue = $complete ? $game['sets_a'] . '-' . $game['sets_b'] : null;

if ($teamA === null || $teamB === null) {
    $options = [];
} elseif ($viewerIsA) {
    $options = [
        ['2-0', 'Sieg 2:0'],
        ['0-2', 'Niederlage 0:2'],
        ['2-1', 'Sieg 2:1'],
        ['1-2', 'Niederlage 1:2'],
    ];
} elseif ($viewerIsB) {
    $options = [
        ['0-2', 'Sieg 2:0'],
        ['2-0', 'Niederlage 0:2'],
        ['1-2', 'Sieg 2:1'],
        ['2-1', 'Niederlage 1:2'],
    ];
} else {
    $options = [
        ['2-0', $teamA['name'] . ' gewinnt 2:0'],
        ['0-2', $teamB['name'] . ' gewinnt 2:0'],
        ['2-1', $teamA['name'] . ' gewinnt 2:1'],
        ['1-2', $teamB['name'] . ' gewinnt 2:1'],
    ];
}
?>
<?php if ($complete): ?>
  <div class="result-chip">
    <span class="score"><?= h(format_result($game, $targetTeamId)) ?></span>
    <?php if (!$hideDate && !empty($game['played_date'])): ?><span class="played-date"><?= h(format_date_ch($game['played_date'])) ?></span><?php endif; ?>
  </div>
<?php elseif ($teamA === null || $teamB === null): ?>
  <span class="result-pending">Gegner steht noch nicht fest</span>
<?php elseif (!empty($game['played_date'])): ?>
  <div class="result-chip result-chip-scheduled">
    <span class="scheduled-label">Geplant</span>
    <?php if (!$hideDate): ?><span class="played-date"><?= h(format_date_ch($game['played_date'])) ?></span><?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($canEdit && $teamA !== null && $teamB !== null && !$complete): ?>
  <details class="result-entry">
    <summary><?= empty($game['played_date']) ? 'Termin festlegen' : 'Termin ändern' ?></summary>
    <form method="post" action="<?= h(url('/game/' . $game['id'] . '/schedule')) ?>" class="score-form">
      <?= csrf_field() ?>
      <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
      <p class="score-hint">Für die anderen Teams sichtbar, sobald gespeichert.</p>
      <label class="date-field">
        <span>Datum</span>
        <input type="date" name="played_date" value="<?= h($game['played_date'] ?? $today) ?>" required>
      </label>
      <button class="btn btn-primary btn-sm" type="submit">Termin speichern</button>
    </form>
  </details>
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
      <p class="score-hint">Zum Speichern das Ergebnis antippen:</p>
      <div class="score-buttons">
        <?php foreach ($options as [$value, $label]): ?>
          <?php $isCurrent = $value === $currentValue; ?>
          <button type="submit" name="score" value="<?= h($value) ?>" class="score-btn<?= $isCurrent ? ' score-btn-current' : '' ?>"><?= h($label) ?><?= $isCurrent ? ' (aktuell)' : '' ?></button>
        <?php endforeach; ?>
      </div>
    </form>
  </details>
<?php endif; ?>
