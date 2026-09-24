<?php
/** @var array $season
 * @var bool $isCurrent
 * @var array[] $items merged games + custom events, from CalendarController::mergedItems()
 * @var array[] $customEvents this season's custom entries, from CalendarEvent::forSeason() -- for the admin management list
 * @var array[] $teamsById
 * @var array[] $groupsById
 * @var int $unscheduledCount
 * @var string $view 'month'|'list'
 * @var string $month 'YYYY-MM'
 * @var string $feedUrl
 * @var array|null $admin
 */
$pageTitle = 'Kalender';
$monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
$weekdayShort = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
$weekdayLong = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
$durationOptions = calendar_duration_options();

$byDate = [];
foreach ($items as $item) {
    $byDate[$item['date']][] = $item;
}

$today = date('Y-m-d');
$baseUrl = url($isCurrent ? '/calendar' : '/calendar/' . $season['id']);
$webcalUrl = preg_replace('#^https?://#', 'webcal://', $feedUrl);

/** Title, subtitle (phase/location) and link target shared by the month-grid chip and the list row. */
$itemDisplay = function (array $item) use ($teamsById, $groupsById, $isCurrent, $season): array {
    if ($item['kind'] === 'game') {
        $g = $item['game'];
        $teamA = $g['team_a_id'] !== null ? ($teamsById[$g['team_a_id']] ?? null) : null;
        $teamB = $g['team_b_id'] !== null ? ($teamsById[$g['team_b_id']] ?? null) : null;
        return [
            'title' => ($teamA['name'] ?? '?') . ' – ' . ($teamB['name'] ?? '?'),
            'subtitle' => game_phase_label($g, $groupsById),
            'href' => $g['phase'] === 'group' && $g['group_id'] !== null
                ? url('/group/' . $g['group_id'])
                : url($isCurrent ? '/bracket' : '/bracket/' . $season['id']),
        ];
    }
    $e = $item['event'];
    return ['title' => $e['title'], 'subtitle' => $e['location'] ?? null, 'href' => null];
};
?>
<div class="page-head">
  <div>
    <?php if (!$isCurrent): ?><p class="eyebrow"><a href="<?= h(url('/archive')) ?>">Archiv</a></p><?php endif; ?>
    <h1>Kalender <?php if (!$isCurrent): ?><span class="badge badge-archive">Archiviert</span><?php endif; ?></h1>
    <p class="muted"><?= h($season['label']) ?></p>
  </div>
</div>

<?php if ($admin !== null && $isCurrent): ?>
  <section class="card">
    <h2>Termin hinzufügen</h2>
    <p class="muted">Für alles ohne eigenes Spiel &ndash; Gruppenauslosung, Siegerehrung usw. Erscheint für alle im Kalender und im ICS-Feed.</p>
    <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/calendar-event')) ?>" class="stack-form calendar-event-form">
      <?= csrf_field() ?>
      <label>
        <span>Titel</span>
        <input type="text" name="title" required maxlength="150">
      </label>
      <div class="field-row">
        <label>
          <span>Datum</span>
          <input type="date" name="event_date" required>
        </label>
        <label>
          <span>Zeit <span class="muted">(optional)</span></span>
          <input type="time" name="event_time">
        </label>
        <label>
          <span>Dauer <span class="muted">(nur bei Uhrzeit)</span></span>
          <select name="duration_minutes">
            <?php foreach ($durationOptions as $minutes => $optLabel): ?>
              <option value="<?= $minutes ?>"<?= $minutes === 60 ? ' selected' : '' ?>><?= h($optLabel) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <label>
        <span>Ort <span class="muted">(optional)</span></span>
        <input type="text" name="location" maxlength="255" placeholder="Strandbad Buochs-Ennetbürgen">
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="reminder" value="1" checked>
        <span>Erinnerung 1 Stunde vorher (nur bei Uhrzeit)</span>
      </label>
      <button class="btn btn-primary btn-sm" type="submit">Termin speichern</button>
    </form>

    <?php if (!empty($customEvents)): ?>
      <ul class="admin-list calendar-event-manage-list">
        <?php foreach ($customEvents as $e): ?>
          <?php $eDuration = $e['duration_minutes'] ?? 60; ?>
          <li class="calendar-event-manage-row">
            <div class="calendar-event-manage-head">
              <span><?= h(format_datetime_ch($e['event_date'], $e['event_time'])) ?> &middot; <strong><?= h($e['title']) ?></strong><?php if (!empty($e['location'])): ?> &middot; <span class="muted"><?= h($e['location']) ?></span><?php endif; ?></span>
              <form method="post" action="<?= h(url('/admin/calendar-event/' . $e['id'] . '/delete')) ?>" class="inline-form">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm" type="submit">Löschen</button>
              </form>
            </div>
            <details class="admin-team-edit">
              <summary>Bearbeiten</summary>
              <form method="post" action="<?= h(url('/admin/calendar-event/' . $e['id'] . '/update')) ?>" class="stack-form">
                <?= csrf_field() ?>
                <label>
                  <span>Titel</span>
                  <input type="text" name="title" value="<?= h($e['title']) ?>" required maxlength="150">
                </label>
                <div class="field-row">
                  <label>
                    <span>Datum</span>
                    <input type="date" name="event_date" value="<?= h($e['event_date']) ?>" required>
                  </label>
                  <label>
                    <span>Zeit <span class="muted">(optional)</span></span>
                    <input type="time" name="event_time" value="<?= h(format_time_ch($e['event_time'])) ?>">
                  </label>
                  <label>
                    <span>Dauer <span class="muted">(nur bei Uhrzeit)</span></span>
                    <select name="duration_minutes">
                      <?php foreach ($durationOptions as $minutes => $optLabel): ?>
                        <option value="<?= $minutes ?>"<?= $minutes === (int) $eDuration ? ' selected' : '' ?>><?= h($optLabel) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                </div>
                <label>
                  <span>Ort <span class="muted">(optional)</span></span>
                  <input type="text" name="location" value="<?= h($e['location'] ?? '') ?>" maxlength="255">
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" name="reminder" value="1" <?= !empty($e['reminder']) ? 'checked' : '' ?>>
                  <span>Erinnerung 1 Stunde vorher (nur bei Uhrzeit)</span>
                </label>
                <button class="btn btn-primary btn-sm" type="submit">Speichern</button>
              </form>
            </details>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
<?php endif; ?>

<section class="calendar-section" id="calendar">
  <nav class="page-head-actions calendar-view-toggle">
    <a class="btn btn-sm <?= $view === 'month' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= h($baseUrl . '?view=month&month=' . $month) ?>#calendar">Monat</a>
    <a class="btn btn-sm <?= $view === 'list' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= h($baseUrl . '?view=list') ?>#calendar">Liste</a>
  </nav>

  <?php if ($unscheduledCount > 0): ?>
    <p class="hint">Für <?= (int) $unscheduledCount ?> Spiel<?= $unscheduledCount === 1 ? '' : 'e' ?> mit bekanntem Gegner ist noch kein Termin festgelegt.</p>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <p class="empty-state">Für diese Saison sind noch keine Termine geplant.</p>
  <?php elseif ($view === 'list'): ?>
    <section class="calendar-list">
      <?php foreach ($byDate as $date => $dayItems): ?>
        <?php $ts = strtotime($date); ?>
        <h2 class="calendar-list-day"><?= h($weekdayLong[(int) date('N', $ts) - 1]) ?>, <?= (int) date('j', $ts) ?>. <?= h($monthNames[(int) date('n', $ts) - 1]) ?> <?= (int) date('Y', $ts) ?></h2>
        <ol class="fixture-list">
          <?php foreach ($dayItems as $item): ?>
            <?php
              $display = $itemDisplay($item);
              $g = $item['kind'] === 'game' ? $item['game'] : null;
            ?>
            <li class="fixture-card">
              <div class="fixture-teams">
                <span class="fixture-num calendar-event-time"><?= $item['time'] ? h(format_time_ch($item['time'])) : 'Ganztägig' ?></span>
                <?php if ($item['kind'] === 'game'): ?>
                  <?php
                    $teamA = $g['team_a_id'] !== null ? ($teamsById[$g['team_a_id']] ?? null) : null;
                    $teamB = $g['team_b_id'] !== null ? ($teamsById[$g['team_b_id']] ?? null) : null;
                  ?>
                  <?php if ($teamA): ?><?= render_partial('partials/team-dot', ['team' => $teamA, 'seasonId' => (int) $season['id']]) ?><?php endif; ?>
                  <span class="vs">–</span>
                  <?php if ($teamB): ?><?= render_partial('partials/team-dot', ['team' => $teamB, 'seasonId' => (int) $season['id']]) ?><?php endif; ?>
                <?php else: ?>
                  <strong><?= h($display['title']) ?></strong>
                <?php endif; ?>
              </div>
              <div class="fixture-result">
                <?php if ($g !== null && Game::isComplete($g)): ?><span class="result-chip"><span class="score"><?= h(format_result($g)) ?></span></span><?php endif; ?>
                <?php if ($display['href'] !== null): ?>
                  <a class="card-link calendar-phase-link" href="<?= h($display['href']) ?>"><?= h($display['subtitle']) ?> →</a>
                <?php elseif ($display['subtitle']): ?>
                  <span class="muted"><?= h($display['subtitle']) ?></span>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endforeach; ?>
    </section>
  <?php else: ?>
    <?php
      $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', $month . '-01');
      $prevMonth = $monthStart->modify('-1 month')->format('Y-m');
      $nextMonth = $monthStart->modify('+1 month')->format('Y-m');
      $monthEnd = $monthStart->modify('last day of this month');
      $gridStart = $monthStart->modify('-' . ((int) $monthStart->format('N') - 1) . ' days');
      $gridEnd = $monthEnd->modify('+' . (7 - (int) $monthEnd->format('N')) . ' days');
    ?>
    <nav class="calendar-nav">
      <a class="calendar-nav-arrow" href="<?= h($baseUrl . '?view=month&month=' . $prevMonth) ?>#calendar" aria-label="Vorheriger Monat">‹</a>
      <h2><?= h($monthNames[(int) $monthStart->format('n') - 1]) ?> <?= h($monthStart->format('Y')) ?></h2>
      <a class="calendar-nav-arrow" href="<?= h($baseUrl . '?view=month&month=' . $nextMonth) ?>#calendar" aria-label="Nächster Monat">›</a>
    </nav>
    <div class="calendar-grid">
      <?php foreach ($weekdayShort as $wd): ?><div class="calendar-weekday"><?= h($wd) ?></div><?php endforeach; ?>
      <?php for ($day = $gridStart; $day <= $gridEnd; $day = $day->modify('+1 day')): ?>
        <?php
          $dateStr = $day->format('Y-m-d');
          $dayItems = $byDate[$dateStr] ?? [];
          $outside = $day->format('Y-m') !== $month;
          $isToday = $dateStr === $today;
        ?>
        <div class="calendar-day<?= $outside ? ' calendar-day-outside' : '' ?><?= $isToday ? ' calendar-day-today' : '' ?>">
          <span class="calendar-day-num"><?= (int) $day->format('j') ?></span>
          <?php foreach ($dayItems as $item): ?>
            <?php
              $display = $itemDisplay($item);
              $title = $display['subtitle'] !== null && $display['subtitle'] !== ''
                  ? $display['title'] . ' (' . $display['subtitle'] . ')'
                  : $display['title'];
              $meta = ($item['time'] ? format_time_ch($item['time']) . ' · ' : '') . ($display['subtitle'] ?? ($item['kind'] === 'event' ? 'Termin' : ''));
            ?>
            <?php if ($display['href'] !== null): ?>
              <a class="calendar-event" href="<?= h($display['href']) ?>" title="<?= h($title) ?>">
                <span class="calendar-event-meta"><?= h($meta) ?></span>
                <span class="calendar-event-teams"><?= h($display['title']) ?></span>
              </a>
            <?php else: ?>
              <div class="calendar-event calendar-event-custom" title="<?= h($title) ?>">
                <span class="calendar-event-meta"><?= h($meta) ?></span>
                <span class="calendar-event-teams"><?= h($display['title']) ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</section>

<section class="card calendar-subscribe">
  <h2>Kalender abonnieren</h2>
  <p class="muted">Damit erscheinen alle geplanten Spiele automatisch in deiner Kalender-App &ndash; neue Termine werden laufend nachgeladen.</p>
  <div class="calendar-subscribe-row">
    <input type="text" class="calendar-feed-url" id="calendar-feed-url" readonly value="<?= h($feedUrl) ?>" onclick="this.select()">
    <button type="button" class="btn btn-secondary btn-sm" id="calendar-feed-copy">Kopieren</button>
    <span class="muted small" id="calendar-feed-copied" hidden>Kopiert!</span>
  </div>
  <p class="hint">
    <a href="<?= h($webcalUrl) ?>">Direkt abonnieren</a> (Apple Kalender, Outlook)
    &middot; für Google Kalender die Adresse oben unter &bdquo;Über URL&ldquo; einfügen.
  </p>
</section>

<script>
(function () {
  var input = document.getElementById('calendar-feed-url');
  var copyBtn = document.getElementById('calendar-feed-copy');
  var copiedNote = document.getElementById('calendar-feed-copied');
  if (!copyBtn) { return; }
  copyBtn.addEventListener('click', function () {
    var showCopied = function () {
      copiedNote.hidden = false;
      setTimeout(function () { copiedNote.hidden = true; }, 1800);
    };
    var fallback = function () {
      input.select();
      document.execCommand('copy');
      showCopied();
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(input.value).then(showCopied, fallback);
    } else {
      fallback();
    }
  });
})();
</script>
