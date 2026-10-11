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
    <strong><?= h(format_datetime_ch($date, $time)) ?></strong> ansetzen.
    <?= count($conflicts) === 1 ? 'Zu diesem Zeitpunkt ist bereits ein Spiel geplant:' : 'Zu diesem Zeitpunkt sind bereits ' . count($conflicts) . ' Spiele geplant:' ?>
  </p>
  <ul class="conflict-list">
    <?php foreach ($conflicts as $other): ?>
      <li>
        <strong><?= h($pairing($other)) ?></strong>
        <span class="muted">· <?= h(game_phase_label($other, $groupsById)) ?></span><br>
        <?= h(format_datetime_ch($other['played_date'], $other['game_time'])) ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($sameDayOnly): ?>
    <p class="muted">Ohne genaue Uhrzeit lässt sich nicht sagen, ob sich die Spiele tatsächlich überschneiden.</p>
  <?php endif; ?>

  <form method="post" action="<?= h(url('/game/' . $game['id'] . '/schedule')) ?>" class="conflict-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
    <input type="hidden" name="played_date" value="<?= h($date) ?>">
    <input type="hidden" name="game_time" value="<?= h(format_time_ch($time)) ?>">
    <input type="hidden" name="confirm_conflict" value="1">
    <a class="btn btn-secondary" href="<?= h(url($returnTo)) ?>">Anderen Termin wählen</a>
    <button class="btn btn-primary" type="submit">Trotzdem speichern</button>
  </form>
</div>
