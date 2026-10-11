<?php
/** @var array $game the game being scheduled
 * @var array[] $conflicts other planned games clashing with the requested slot (see Game::scheduleConflicts)
 * @var array[] $teamsById
 * @var array[] $groupsById
 * @var string $date requested date (Y-m-d)
 * @var string|null $time requested time (H:i:s) or null
 * @var string $returnTo
 */
$pageTitle = 'Terminkonflikt';
$pairing = static fn (array $g): string => $teamsById[$g['team_a_id']]['name'] . ' – ' . $teamsById[$g['team_b_id']]['name'];
$sameDayOnly = $time === null;
foreach ($conflicts as $other) {
    $sameDayOnly = $sameDayOnly || $other['game_time'] === null;
}
$toMinutes = static fn (string $t): int => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
$clock = static fn (int $m): string => sprintf('%02d:%02d', intdiv($m, 60) % 24, $m % 60);
/** "18:00 – 19:00 (1 Std.)" for two timed slots, or null when either game has no time. */
$overlap = static function (?string $otherTime) use ($time, $toMinutes, $clock): ?string {
    if ($time === null || $otherTime === null) {
        return null;
    }
    $start = max($toMinutes($time), $toMinutes($otherTime));
    $end = min($toMinutes($time), $toMinutes($otherTime)) + Game::SLOT_MINUTES;
    $length = $end - $start;
    $duration = trim((intdiv($length, 60) > 0 ? intdiv($length, 60) . ' Std. ' : '') . ($length % 60 > 0 ? $length % 60 . ' Min.' : ''));
    return $clock($start) . ' – ' . $clock($end) . ' (' . $duration . ')';
};

// Day timeline: only the timed games can be drawn, on a window of whole hours around all slots.
$timedConflicts = $time === null ? [] : array_values(array_filter($conflicts, static fn (array $g): bool => $g['game_time'] !== null));
if ($timedConflicts !== []) {
    $newStart = $toMinutes($time);
    $starts = array_merge([$newStart], array_map(static fn (array $g): int => $toMinutes($g['game_time']), $timedConflicts));
    $windowStart = intdiv(min($starts), 60) * 60;
    $windowEnd = (int) ceil((max($starts) + Game::SLOT_MINUTES) / 60) * 60;
    $windowHours = intdiv($windowEnd - $windowStart, 60);
    /** CSS left/width of a [$from, $to) minute range within the window. */
    $span = static fn (int $from, int $to): string => sprintf(
        'left:%.3F%%;width:%.3F%%',
        ($from - $windowStart) / ($windowEnd - $windowStart) * 100,
        ($to - $from) / ($windowEnd - $windowStart) * 100
    );
}
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><?= h(game_phase_label($game, $groupsById)) ?></p>
    <h1>Terminkonflikt</h1>
  </div>
</div>

<div class="card conflict-card">
  <p>
    Du möchtest <strong><?= h($pairing($game)) ?></strong> am
    <strong><?= h(format_game_slot_ch($date, $time)) ?></strong> ansetzen.
    <?= count($conflicts) === 1 ? 'Zu diesem Zeitpunkt ist bereits ein Spiel geplant:' : 'Zu diesem Zeitpunkt sind bereits ' . count($conflicts) . ' Spiele geplant:' ?>
  </p>
  <ul class="conflict-list">
    <?php foreach ($conflicts as $other): ?>
      <li>
        <strong><?= h($pairing($other)) ?></strong>
        <span class="muted">· <?= h(game_phase_label($other, $groupsById)) ?></span><br>
        <?= h(format_game_slot_ch($other['played_date'], $other['game_time'])) ?>
        <?php $overlapText = $overlap($other['game_time']); ?>
        <?php if ($overlapText !== null): ?><br><span class="conflict-overlap">Überschneidung: <?= h($overlapText) ?></span><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($timedConflicts !== []): ?>
    <div class="slot-timeline" style="--hours: <?= $windowHours ?>" aria-hidden="true">
      <div class="slot-row">
        <span class="slot-label">Neu: <?= h($pairing($game)) ?></span>
        <div class="slot-track">
          <div class="slot-bar slot-bar-new" style="<?= $span($newStart, $newStart + Game::SLOT_MINUTES) ?>"><span><?= h($clock($newStart) . ' – ' . $clock($newStart + Game::SLOT_MINUTES)) ?></span></div>
        </div>
      </div>
      <?php foreach ($timedConflicts as $other): ?>
        <?php
          $otherStart = $toMinutes($other['game_time']);
          $overlapStart = max($newStart, $otherStart);
          $overlapEnd = min($newStart, $otherStart) + Game::SLOT_MINUTES;
        ?>
        <div class="slot-row">
          <span class="slot-label"><?= h($pairing($other)) ?></span>
          <div class="slot-track">
            <div class="slot-bar slot-bar-other" style="<?= $span($otherStart, $otherStart + Game::SLOT_MINUTES) ?>"><span><?= h($clock($otherStart) . ' – ' . $clock($otherStart + Game::SLOT_MINUTES)) ?></span></div>
            <div class="slot-overlap" style="<?= $span($overlapStart, $overlapEnd) ?>"></div>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="slot-axis">
        <?php for ($m = $windowStart; $m <= $windowEnd; $m += 60): ?>
          <span style="left:<?= sprintf('%.3F', ($m - $windowStart) / ($windowEnd - $windowStart) * 100) ?>%"><?= h($clock($m)) ?></span>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
  <p class="muted">Ein Spiel dauert erfahrungsgemäss rund <?= intdiv(Game::SLOT_MINUTES, 60) ?> Stunden – die Zeitfenster oben gehen davon aus. Ob es tatsächlich knapp wird, könnt ihr am besten selbst abschätzen.</p>
  <?php if ($sameDayOnly): ?>
    <p class="muted">Ohne genaue Uhrzeit lässt sich nicht sagen, ob sich die Spiele tatsächlich überschneiden.</p>
  <?php endif; ?>

  <form method="post" action="<?= h(url('/game/' . $game['id'] . '/schedule')) ?>" class="conflict-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
    <input type="hidden" name="played_date" value="<?= h($date) ?>">
    <input type="hidden" name="game_time" value="<?= h(format_time_ch($time)) ?>">
    <input type="hidden" name="confirm_conflict" value="1">
    <button class="btn btn-primary" type="submit">Trotzdem speichern</button>
  </form>

  <h2 class="conflict-subhead">Anderen Termin wählen</h2>
  <?php // Submitted without confirm_conflict, so the new slot is checked again before saving. ?>
  <form method="post" action="<?= h(url('/game/' . $game['id'] . '/schedule')) ?>" class="score-form">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
    <label class="date-field">
      <span>Datum</span>
      <input type="date" name="played_date" value="<?= h($date) ?>" required>
    </label>
    <label class="date-field">
      <span>Zeit <span class="muted">(optional)</span></span>
      <input type="time" name="game_time" value="<?= h(format_time_ch($time)) ?>">
    </label>
    <div class="conflict-actions">
      <a class="btn btn-secondary btn-sm" href="<?= h(url($returnTo)) ?>">Abbrechen</a>
      <button class="btn btn-primary btn-sm" type="submit">Termin speichern</button>
    </div>
  </form>
</div>
