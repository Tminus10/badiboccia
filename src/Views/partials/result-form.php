<?php
/** @var array $game
 * @var array|null $teamA
 * @var array|null $teamB
 * @var string $returnTo
 * @var int|null $viewerTeamId
 * @var bool $canEdit
 * @var int|null $targetTeamId whose page this is being shown on, for score orientation (null = absolute team A:B score)
 * @var bool|null $hideDate omit the date from the chip (caller renders it separately)
 * @var int|null $seasonYear the season's year, used to default the "Termin festlegen" date picker to the right year for past seasons
 */
$targetTeamId = $targetTeamId ?? null;
$hideDate = $hideDate ?? false;
$complete = Game::isComplete($game);
// Whose name goes on the left: the page we're on (a team's own page) takes priority over
// who happens to be logged in, so admins/spectators see the same layout as the team itself.
$orientTeamId = $targetTeamId ?? $viewerTeamId;
$orientIsB = $orientTeamId !== null && $teamB !== null && (int) $game['team_b_id'] === $orientTeamId;
$today = date('Y-m-d');
$scheduleDefaultDate = sprintf('%04d-07-15', $seasonYear ?? (int) date('Y'));
if ($scheduleDefaultDate > $today) {
    $scheduleDefaultDate = $today;
}
$currentValue = $complete ? $game['sets_a'] . '-' . $game['sets_b'] : null;
// A tie (1:1) only makes sense in the group phase -- knockout games need a winner to advance.
$allowTie = ($game['phase'] ?? 'group') === 'group';

if ($teamA === null || $teamB === null) {
    $options = [];
} else {
    // Left column is always the oriented team (see $orientTeamId above); right is the opponent.
    if ($orientIsB) {
        [$leftTeam, $rightTeam] = [$teamB, $teamA];
        [$leftWin20, $leftWin21, $rightWin20, $rightWin21] = ['0-2', '1-2', '2-0', '2-1'];
    } else {
        [$leftTeam, $rightTeam] = [$teamA, $teamB];
        [$leftWin20, $leftWin21, $rightWin20, $rightWin21] = ['2-0', '2-1', '0-2', '1-2'];
    }
    $options = [
        [$leftWin20, $leftTeam['name'] . ' gewinnt 2:0'],
        [$rightWin20, $rightTeam['name'] . ' gewinnt 2:0'],
        [$leftWin21, $leftTeam['name'] . ' gewinnt 2:1'],
        [$rightWin21, $rightTeam['name'] . ' gewinnt 2:1'],
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
        <input type="date" name="played_date" value="<?= h($game['played_date'] ?? $scheduleDefaultDate) ?>" required>
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
      <?php $resultDateDefault = $game['played_date'] ?? $scheduleDefaultDate; ?>
      <input type="hidden" name="played_date_default" value="<?= h($resultDateDefault) ?>">
      <label class="date-field">
        <span>Datum <span class="muted">(optional, falls bekannt)</span></span>
        <input type="date" name="played_date" value="<?= h($resultDateDefault) ?>" max="<?= h($today) ?>">
      </label>
      <p class="score-hint">Zum Speichern das Ergebnis antippen:</p>
      <div class="score-buttons">
        <?php foreach ($options as [$value, $label]): ?>
          <?php $isCurrent = $value === $currentValue; ?>
          <button type="submit" name="score" value="<?= h($value) ?>" class="score-btn<?= $isCurrent ? ' score-btn-current' : '' ?>"><?= h($label) ?><?= $isCurrent ? ' (aktuell)' : '' ?></button>
        <?php endforeach; ?>
        <?php if ($allowTie): ?>
          <?php $isCurrentTie = $currentValue === '1-1'; ?>
          <button type="submit" name="score" value="1-1" class="score-btn score-btn-tie<?= $isCurrentTie ? ' score-btn-current' : '' ?>">Unentschieden 1:1<?= $isCurrentTie ? ' (aktuell)' : '' ?></button>
        <?php endif; ?>
      </div>
    </form>
  </details>
<?php endif; ?>
