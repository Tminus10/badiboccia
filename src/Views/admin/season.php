<?php
/** @var array $admin
 * @var array $season
 * @var array[] $groupData
 * @var array[] $qfGames
 * @var array<int, array{0:string,1:string}> $qfLabelSuggestions
 * @var array[] $qualifiedTeams
 * @var int $qualifiersPerGroup
 * @var array[] $availableTeams
 */
$pageTitle = t('Verwalten') . ' – ' . $season['label'];
$isCurrent = (int) $season['is_current'] === 1;

// Plain-text team/PIN list for copy-pasting (e.g. into WhatsApp) -- built once here so the
// on-screen textarea and the printable version below both show exactly the same data.
$overviewGroups = array_values(array_filter($groupData, fn ($entry) => !empty($entry['teams'])));
$overviewLines = ['Boccia ' . $season['label'] . ' – ' . t('Teams & PINs'), ''];
foreach ($overviewGroups as $entry) {
    $overviewLines[] = t('Gruppe %s', $entry['group']['name']);
    foreach ($entry['teams'] as $team) {
        $overviewLines[] = $team['name'] . ' – PIN ' . $team['pin'];
    }
    $overviewLines[] = '';
}
$overviewText = rtrim(implode("\n", $overviewLines));
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url('/admin')) ?>"><?= h(t('Admin')) ?></a></p>
    <h1><?= h($season['label']) ?></h1>
    <?php if ($isCurrent): ?><span class="badge badge-current"><?= h(t('Aktive Saison')) ?></span><?php else: ?><span class="badge badge-archive"><?= h(t('Archiviert')) ?></span><?php endif; ?>
  </div>
  <div class="page-head-actions">
    <details class="season-edit-entry">
      <summary class="btn btn-secondary btn-sm">✎ <?= h(t('Bezeichnung / Jahr bearbeiten')) ?></summary>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/update')) ?>" class="stack-form">
        <?= csrf_field() ?>
        <label><?= h(t('Bezeichnung')) ?> <input type="text" name="label" value="<?= h($season['label']) ?>" required></label>
        <label><?= h(t('Jahr')) ?> <input type="number" name="year" value="<?= (int) $season['year'] ?>" required></label>
        <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Speichern')) ?></button>
      </form>

      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Saison «%s» wirklich unwiderruflich löschen? Alle Gruppen, Spiele und Resultate dieser Saison gehen dabei verloren.', $season['label']))) ?>);">
        <?= csrf_field() ?>
        <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Saison löschen')) ?></button>
      </form>
    </details>
    <?php if ($isCurrent): ?>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/deactivate')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Deaktivieren')) ?></button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/activate')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Als aktiv setzen')) ?></button>
      </form>
    <?php endif; ?>
    <a class="btn btn-secondary" href="<?= h(url('/admin/season/' . $season['id'] . '/audit')) ?>"><?= h(t('Änderungsprotokoll')) ?></a>
  </div>
</div>

<?php foreach ($groupData as $entry): ?>
  <?php $group = $entry['group']; ?>
  <section class="card" id="group-<?= (int) $group['id'] ?>">
    <header class="group-card-head">
      <h2><?= h(t('Gruppe %s', $group['name'])) ?></h2>
      <div class="group-card-actions">
        <?php if ($entry['gamesCount'] === 0): ?>
          <form method="post" action="<?= h(url('/admin/group/' . $group['id'] . '/fixtures')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Spielplan für Gruppe %s jetzt erstellen? Danach können keine weiteren Teams mehr sinnvoll ergänzt werden.', $group['name']))) ?>);">
            <?= csrf_field() ?>
            <button class="btn btn-secondary btn-sm" type="submit" <?= count($entry['teams']) < 2 ? 'disabled' : '' ?>><?= h(t('Spielplan erstellen')) ?></button>
          </form>
        <?php else: ?>
          <span class="muted"><?= h(t('%d Spiele erstellt', (int) $entry['gamesCount'])) ?></span>
        <?php endif; ?>
        <?php if (empty($entry['teams']) && $entry['gamesCount'] === 0): ?>
          <form method="post" action="<?= h(url('/admin/group/' . $group['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Ungenutzte Gruppe %s löschen?', $group['name']))) ?>);">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Gruppe löschen')) ?></button>
          </form>
        <?php endif; ?>
      </div>
    </header>

    <?php if (empty($entry['teams'])): ?>
      <p class="empty-state small"><?= h(t('Noch keine Teams.')) ?></p>
    <?php else: ?>
      <ul class="admin-team-list">
        <?php foreach ($entry['teams'] as $team): ?>
          <li class="admin-team-row">
            <?= render_partial('partials/team-avatar', ['team' => $team, 'size' => 'sm', 'linked' => true, 'seasonId' => (int) $season['id']]) ?>
            <div class="admin-team-info">
              <strong><?= h($team['name']) ?></strong>
              <?php $subtitle = team_players_subtitle($team); ?>
              <?php if ($subtitle !== null): ?><span class="muted"><?= h($subtitle) ?></span><?php endif; ?>
              <span class="muted pin-display">PIN: <strong><?= h($team['pin']) ?></strong></span>
            </div>
            <details class="admin-team-edit">
              <summary><?= h(t('Bearbeiten')) ?></summary>

              <form method="post" action="<?= h(url('/admin/team/' . $team['id'])) ?>" class="stack-form">
                <?= csrf_field() ?>
                <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                <label><?= h(t('Teamname')) ?> <input type="text" name="name" value="<?= h($team['name']) ?>" required></label>
                <label><?= h(t('Spieler %d', 1)) ?> <input type="text" name="player1" value="<?= h($team['player1']) ?>" required></label>
                <label><?= h(t('Spieler %d', 2)) ?> <input type="text" name="player2" value="<?= h($team['player2']) ?>" required></label>
                <?php if ($isCurrent): ?>
                  <label><?= h(t('Gruppe')) ?>
                    <select name="group_id">
                      <?php foreach ($groupData as $opt): ?>
                        <option value="<?= (int) $opt['group']['id'] ?>" <?= (int) $opt['group']['id'] === (int) $team['group_id'] ? 'selected' : '' ?>><?= h(t('Gruppe %s', $opt['group']['name'])) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                <?php endif; ?>
                <p class="muted"><?= h(t('Name, Spieler, PIN und Foto gelten für dieses Team über alle Saisons hinweg.')) ?></p>
                <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Speichern')) ?></button>
              </form>

              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/photo')) ?>" enctype="multipart/form-data" class="stack-form">
                <?= csrf_field() ?>
                <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                <label><?= h(t('Foto')) ?> <input type="file" name="photo" accept="image/png,image/jpeg,image/webp"></label>
                <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Foto hochladen')) ?></button>
              </form>

              <div class="admin-team-actions">
                <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/pin')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Neuen PIN für %s erstellen? Der alte PIN wird ungültig – in jeder Saison.', $team['name']))) ?>);">
                  <?= csrf_field() ?>
                  <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                  <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('PIN zurücksetzen')) ?></button>
                </form>
                <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm(<?= h(json_encode(t('Team %s aus dieser Saison entfernen? Das Team bleibt erhalten, wenn es auch in anderen Saisons mitspielt.', $team['name']))) ?>);">
                  <?= csrf_field() ?>
                  <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                  <button class="btn btn-ghost btn-sm btn-danger" type="submit"><?= h(t('Aus Saison entfernen')) ?></button>
                </form>
              </div>
            </details>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($isCurrent): ?>
      <?php if (!empty($availableTeams)): ?>
        <details class="add-form">
          <summary>+ <?= h(t('Bestehendes Team hinzufügen')) ?></summary>
          <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/team/enroll')) ?>" class="stack-form">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
            <label><?= h(t('Team')) ?>
              <select name="team_id" required>
                <option value="">– <?= h(t('Team wählen')) ?> –</option>
                <?php foreach ($availableTeams as $t): ?>
                  <option value="<?= (int) $t['id'] ?>"><?= h($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn btn-secondary btn-sm" type="submit"><?= h(t('Team hinzufügen')) ?></button>
          </form>
        </details>
      <?php endif; ?>

      <details class="add-form">
        <summary>+ <?= h(t('Neues Team erstellen')) ?></summary>
        <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/team')) ?>" class="stack-form">
          <?= csrf_field() ?>
          <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
          <label><?= h(t('Teamname')) ?> <input type="text" name="name" placeholder="<?= h(t('z. B. %s', 'Debi & Erika')) ?>" required></label>
          <label><?= h(t('Spieler %d', 1)) ?> <input type="text" name="player1" required></label>
          <label><?= h(t('Spieler %d', 2)) ?> <input type="text" name="player2" required></label>
          <button class="btn btn-primary btn-sm" type="submit"><?= h(t('Team erstellen')) ?></button>
        </form>
      </details>
    <?php endif; ?>
  </section>
<?php endforeach; ?>

<section class="card">
  <h2><?= h(t('Viertelfinal-Paarungen')) ?></h2>
  <?php if ($qualifiersPerGroup === 2): ?>
    <p class="muted"><?= h(t('Weise die acht qualifizierten Teams (Erste & Zweite jeder Gruppe) den vier Viertelfinal-Spielen zu. Noch offene Paarungen sind bereits mit einem Vorschlag vorausgefüllt (Erste gegen Zweite einer anderen Gruppe, über Kreuz, damit zwei Teams derselben Gruppe erst im Final erneut aufeinandertreffen können) – einfach bei Bedarf über die Dropdowns anpassen.')) ?></p>
  <?php else: ?>
    <p class="muted"><?= h(t('Weise acht der %1$d qualifizierten Teams (die besten %2$d jeder Gruppe) den vier Viertelfinal-Spielen zu.', count($qualifiedTeams), $qualifiersPerGroup)) ?></p>
  <?php endif; ?>
  <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/bracket')) ?>" class="stack-form qf-form">
    <?= csrf_field() ?>
    <?php foreach ($qfGames as $game): ?>
      <?php $slot = (int) $game['slot_index']; ?>
      <?php $labelSuggestion = $qfLabelSuggestions[$slot] ?? null; ?>
      <div class="qf-pair">
        <div class="qf-pair-title">
          <?= h(t('Viertelfinal %d', $slot)) ?>
          <?php if (!empty($game['prefilled']) || $labelSuggestion !== null): ?><span class="badge badge-suggestion"><?= h(t('Vorschlag')) ?></span><?php endif; ?>
        </div>
        <div class="qf-pair-row">
          <select name="qf<?= $slot ?>_a">
            <option value=""><?= $labelSuggestion !== null ? h($labelSuggestion[0]) : '– ' . h(t('Team A wählen')) . ' –' ?></option>
            <?php foreach ($qualifiedTeams as $t): ?>
              <option value="<?= (int) $t['id'] ?>" <?= (int) $game['team_a_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= h($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="vs"><?= h(t('vs.')) ?></span>
          <select name="qf<?= $slot ?>_b">
            <option value=""><?= $labelSuggestion !== null ? h($labelSuggestion[1]) : '– ' . h(t('Team B wählen')) . ' –' ?></option>
            <?php foreach ($qualifiedTeams as $t): ?>
              <option value="<?= (int) $t['id'] ?>" <?= (int) $game['team_b_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= h($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    <?php endforeach; ?>
    <button class="btn btn-primary" type="submit"><?= h(t('Paarungen speichern')) ?></button>
  </form>
</section>

<?php if (!empty($overviewGroups)): ?>
<section class="card team-overview-card">
  <h2><?= h(t('Team-Übersicht (Name & PIN)')) ?></h2>
  <p class="muted"><?= h(t('Zum Kopieren in die WhatsApp-Gruppe oder zum Ausdrucken.')) ?></p>
  <div class="team-overview-actions">
    <button type="button" class="btn btn-secondary btn-sm" id="team-overview-copy"><?= h(t('Kopieren')) ?></button>
    <button type="button" class="btn btn-secondary btn-sm" id="team-overview-print"><?= h(t('Drucken')) ?></button>
    <span class="muted small" id="team-overview-copied" hidden><?= h(t('Kopiert!')) ?></span>
  </div>
  <textarea id="team-overview-text" class="team-overview-textarea" readonly rows="<?= max(6, substr_count($overviewText, "\n") + 2) ?>"><?= h($overviewText) ?></textarea>

  <div class="team-overview-print-root">
    <h1><?= h($season['label']) ?> &ndash; <?= h(t('Teams & PINs')) ?></h1>
    <?php foreach ($overviewGroups as $entry): ?>
      <section class="print-group">
        <h2><?= h(t('Gruppe %s', $entry['group']['name'])) ?></h2>
        <table class="print-team-table">
          <?php foreach ($entry['teams'] as $team): ?>
            <tr>
              <td><?= h($team['name']) ?></td>
              <td class="print-pin">PIN <?= h($team['pin']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </section>
    <?php endforeach; ?>
  </div>
</section>
<script>
(function () {
  var copyBtn = document.getElementById('team-overview-copy');
  var printBtn = document.getElementById('team-overview-print');
  var textarea = document.getElementById('team-overview-text');
  var copiedNote = document.getElementById('team-overview-copied');

  copyBtn.addEventListener('click', function () {
    var showCopied = function () {
      copiedNote.hidden = false;
      setTimeout(function () { copiedNote.hidden = true; }, 1800);
    };
    var fallback = function () {
      textarea.select();
      document.execCommand('copy');
      showCopied();
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(textarea.value).then(showCopied, fallback);
    } else {
      fallback();
    }
  });

  printBtn.addEventListener('click', function () {
    document.body.classList.add('printing-team-overview');
    window.print();
  });
  window.addEventListener('afterprint', function () {
    document.body.classList.remove('printing-team-overview');
  });
})();
</script>
<?php endif; ?>
