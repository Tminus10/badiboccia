<?php
/** @var array $admin
 * @var array $season
 * @var array[] $groupData
 * @var array[] $qfGames
 * @var array[] $qualifiedTeams
 * @var int $qualifiersPerGroup
 * @var array[] $availableTeams
 */
$pageTitle = 'Verwalten – ' . $season['label'];
$isCurrent = (int) $season['is_current'] === 1;

// Plain-text team/PIN list for copy-pasting (e.g. into WhatsApp) -- built once here so the
// on-screen textarea and the printable version below both show exactly the same data.
$overviewGroups = array_values(array_filter($groupData, fn ($entry) => !empty($entry['teams'])));
$overviewLines = ['Boccia ' . $season['label'] . ' – Teams & PINs', ''];
foreach ($overviewGroups as $entry) {
    $overviewLines[] = 'Gruppe ' . $entry['group']['name'];
    foreach ($entry['teams'] as $team) {
        $overviewLines[] = $team['name'] . ' – PIN ' . $team['pin'];
    }
    $overviewLines[] = '';
}
$overviewText = rtrim(implode("\n", $overviewLines));
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url('/admin')) ?>">Admin</a></p>
    <h1><?= h($season['label']) ?></h1>
    <?php if ($isCurrent): ?><span class="badge badge-current">Aktive Saison</span><?php else: ?><span class="badge badge-archive">Archiviert</span><?php endif; ?>
  </div>
  <div class="page-head-actions">
    <details class="season-edit-entry">
      <summary class="btn btn-secondary btn-sm">✎ Bezeichnung / Jahr bearbeiten</summary>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/update')) ?>" class="stack-form">
        <?= csrf_field() ?>
        <label>Bezeichnung <input type="text" name="label" value="<?= h($season['label']) ?>" required></label>
        <label>Jahr <input type="number" name="year" value="<?= (int) $season['year'] ?>" required></label>
        <button class="btn btn-primary btn-sm" type="submit">Speichern</button>
      </form>

      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Saison \'<?= h(addslashes($season['label'])) ?>\' wirklich unwiderruflich löschen? Alle Gruppen, Spiele und Resultate dieser Saison gehen dabei verloren.');">
        <?= csrf_field() ?>
        <button class="btn btn-ghost btn-sm btn-danger" type="submit">Saison löschen</button>
      </form>
    </details>
    <?php if ($isCurrent): ?>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/deactivate')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-secondary btn-sm" type="submit">Deaktivieren</button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/activate')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-secondary btn-sm" type="submit">Als aktiv setzen</button>
      </form>
    <?php endif; ?>
    <a class="btn btn-secondary" href="<?= h(url('/admin/season/' . $season['id'] . '/audit')) ?>">Änderungsprotokoll</a>
  </div>
</div>

<?php if (!empty($overviewGroups)): ?>
<section class="card team-overview-card">
  <h2>Team-Übersicht (Name &amp; PIN)</h2>
  <p class="muted">Zum Kopieren in die WhatsApp-Gruppe oder zum Ausdrucken.</p>
  <div class="team-overview-actions">
    <button type="button" class="btn btn-secondary btn-sm" id="team-overview-copy">Kopieren</button>
    <button type="button" class="btn btn-secondary btn-sm" id="team-overview-print">Drucken</button>
    <span class="muted small" id="team-overview-copied" hidden>Kopiert!</span>
  </div>
  <textarea id="team-overview-text" class="team-overview-textarea" readonly rows="<?= max(6, substr_count($overviewText, "\n") + 2) ?>"><?= h($overviewText) ?></textarea>

  <div class="team-overview-print-root">
    <h1><?= h($season['label']) ?> &ndash; Teams &amp; PINs</h1>
    <?php foreach ($overviewGroups as $entry): ?>
      <section class="print-group">
        <h2>Gruppe <?= h($entry['group']['name']) ?></h2>
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

<?php foreach ($groupData as $entry): ?>
  <?php $group = $entry['group']; ?>
  <section class="card" id="group-<?= (int) $group['id'] ?>">
    <header class="group-card-head">
      <h2>Gruppe <?= h($group['name']) ?></h2>
      <div class="group-card-actions">
        <?php if ($entry['gamesCount'] === 0): ?>
          <form method="post" action="<?= h(url('/admin/group/' . $group['id'] . '/fixtures')) ?>" class="inline-form" onsubmit="return confirm('Spielplan für Gruppe <?= h($group['name']) ?> jetzt erstellen? Danach können keine weiteren Teams mehr sinnvoll ergänzt werden.');">
            <?= csrf_field() ?>
            <button class="btn btn-secondary btn-sm" type="submit" <?= count($entry['teams']) < 2 ? 'disabled' : '' ?>>Spielplan erstellen</button>
          </form>
        <?php else: ?>
          <span class="muted"><?= (int) $entry['gamesCount'] ?> Spiele erstellt</span>
        <?php endif; ?>
        <?php if (empty($entry['teams']) && $entry['gamesCount'] === 0): ?>
          <form method="post" action="<?= h(url('/admin/group/' . $group['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Ungenutzte Gruppe <?= h($group['name']) ?> löschen?');">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-sm btn-danger" type="submit">Gruppe löschen</button>
          </form>
        <?php endif; ?>
      </div>
    </header>

    <?php if (empty($entry['teams'])): ?>
      <p class="empty-state small">Noch keine Teams.</p>
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
              <summary>Bearbeiten</summary>

              <form method="post" action="<?= h(url('/admin/team/' . $team['id'])) ?>" class="stack-form">
                <?= csrf_field() ?>
                <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                <label>Teamname <input type="text" name="name" value="<?= h($team['name']) ?>" required></label>
                <label>Spieler 1 <input type="text" name="player1" value="<?= h($team['player1']) ?>" required></label>
                <label>Spieler 2 <input type="text" name="player2" value="<?= h($team['player2']) ?>" required></label>
                <?php if ($isCurrent): ?>
                  <label>Gruppe
                    <select name="group_id">
                      <?php foreach ($groupData as $opt): ?>
                        <option value="<?= (int) $opt['group']['id'] ?>" <?= (int) $opt['group']['id'] === (int) $team['group_id'] ? 'selected' : '' ?>>Gruppe <?= h($opt['group']['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                <?php endif; ?>
                <p class="muted">Name, Spieler, PIN und Foto gelten für dieses Team über alle Saisons hinweg.</p>
                <button class="btn btn-primary btn-sm" type="submit">Speichern</button>
              </form>

              <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/photo')) ?>" enctype="multipart/form-data" class="stack-form">
                <?= csrf_field() ?>
                <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                <label>Foto <input type="file" name="photo" accept="image/png,image/jpeg,image/webp"></label>
                <button class="btn btn-secondary btn-sm" type="submit">Foto hochladen</button>
              </form>

              <div class="admin-team-actions">
                <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/pin')) ?>" class="inline-form" onsubmit="return confirm('Neuen PIN für <?= h(addslashes($team['name'])) ?> erstellen? Der alte PIN wird ungültig – in jeder Saison.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                  <button class="btn btn-ghost btn-sm" type="submit">PIN zurücksetzen</button>
                </form>
                <form method="post" action="<?= h(url('/admin/team/' . $team['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Team <?= h(addslashes($team['name'])) ?> aus dieser Saison entfernen? Das Team bleibt erhalten, wenn es auch in anderen Saisons mitspielt.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="season_id" value="<?= (int) $season['id'] ?>">
                  <button class="btn btn-ghost btn-sm btn-danger" type="submit">Aus Saison entfernen</button>
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
          <summary>+ Bestehendes Team hinzufügen</summary>
          <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/team/enroll')) ?>" class="stack-form">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
            <label>Team
              <select name="team_id" required>
                <option value="">– Team wählen –</option>
                <?php foreach ($availableTeams as $t): ?>
                  <option value="<?= (int) $t['id'] ?>"><?= h($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn btn-secondary btn-sm" type="submit">Team hinzufügen</button>
          </form>
        </details>
      <?php endif; ?>

      <details class="add-form">
        <summary>+ Neues Team erstellen</summary>
        <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/team')) ?>" class="stack-form">
          <?= csrf_field() ?>
          <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
          <label>Teamname <input type="text" name="name" placeholder="z.B. Debi &amp; Erika" required></label>
          <label>Spieler 1 <input type="text" name="player1" required></label>
          <label>Spieler 2 <input type="text" name="player2" required></label>
          <button class="btn btn-primary btn-sm" type="submit">Team erstellen</button>
        </form>
      </details>
    <?php endif; ?>
  </section>
<?php endforeach; ?>

<section class="card">
  <h2>Viertelfinal-Paarungen</h2>
  <?php if ($qualifiersPerGroup === 2): ?>
    <p class="muted">Weise die acht qualifizierten Teams (Erste &amp; Zweite jeder Gruppe) den vier Viertelfinal-Spielen zu. Noch offene Paarungen sind bereits mit einem Vorschlag vorausgefüllt (Erste gegen Zweite einer anderen Gruppe, über Kreuz, damit zwei Teams derselben Gruppe erst im Final erneut aufeinandertreffen können) &ndash; einfach bei Bedarf über die Dropdowns anpassen.</p>
  <?php else: ?>
    <p class="muted">Weise acht der <?= count($qualifiedTeams) ?> qualifizierten Teams (die besten <?= $qualifiersPerGroup ?> jeder Gruppe) den vier Viertelfinal-Spielen zu.</p>
  <?php endif; ?>
  <form method="post" action="<?= h(url('/admin/season/' . $season['id'] . '/bracket')) ?>" class="stack-form">
    <?= csrf_field() ?>
    <?php foreach ($qfGames as $game): ?>
      <?php $slot = (int) $game['slot_index']; ?>
      <div class="qf-pair">
        <span class="qf-label">Viertelfinal <?= $slot ?></span>
        <?php if (!empty($game['prefilled'])): ?><span class="badge badge-suggestion">Vorschlag</span><?php endif; ?>
        <select name="qf<?= $slot ?>_a">
          <option value="">– Team A wählen –</option>
          <?php foreach ($qualifiedTeams as $t): ?>
            <option value="<?= (int) $t['id'] ?>" <?= (int) $game['team_a_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= h($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <span class="vs">–</span>
        <select name="qf<?= $slot ?>_b">
          <option value="">– Team B wählen –</option>
          <?php foreach ($qualifiedTeams as $t): ?>
            <option value="<?= (int) $t['id'] ?>" <?= (int) $game['team_b_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= h($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>
    <button class="btn btn-primary" type="submit">Paarungen speichern</button>
  </form>
</section>
