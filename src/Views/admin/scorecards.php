<?php
/** Standalone print page (no site layout): one A4 scorecard per group, to hang up at the Badi.
 * @var array $season
 * @var array[] $cards each ['group' => array, 'teams' => array[], 'pairings' => array<int, array{0:array,1:array}>]
 * @var int $groupCount
 */
if ($groupCount === 4) {
    $qualificationRule = t('Die Ersten und Zweiten jeder Gruppe kommen ins Viertelfinal.');
} elseif ($groupCount === 3) {
    $qualificationRule = t('Die Ersten und Zweiten jeder Gruppe sowie die zwei besten Gruppendritten kommen ins Viertelfinal.');
} else {
    $qualificationRule = t('Die besten %d jeder Gruppe kommen ins Viertelfinal.', Season::qualifiersPerGroup($groupCount));
}
$defaultDeadline = t('ca. 31. Juli');
$siteUrl = preg_replace('#^https?://#', '', rtrim(absolute_url('/'), '/'));
?><!doctype html>
<html lang="<?= h(I18n::htmlLang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h(t('Spielblätter')) ?> – <?= h($season['label']) ?></title>
<link rel="stylesheet" href="<?= h(asset_url('/assets/css/scorecards.css')) ?>">
</head>
<body>

<div class="toolbar">
  <a href="<?= h(url('/admin/season/' . $season['id'])) ?>">← <?= h($season['label']) ?></a>
  <label><?= h(t('Gruppenspiele fertig bis')) ?>
    <input type="text" id="deadline-input" value="<?= h($defaultDeadline) ?>">
  </label>
  <button type="button" onclick="window.print()"><?= h(t('Drucken / als PDF sichern')) ?></button>
  <p><?= h(t('Tipp: Im Druckdialog Papierformat A4 wählen und «Hintergrundgrafiken» aktivieren, damit die Teamfarben mitgedruckt werden.')) ?></p>
</div>

<?php if (empty($cards)): ?>
  <p class="empty"><?= h(t('Noch keine Teams in dieser Saison.')) ?></p>
<?php endif; ?>

<?php foreach ($cards as $card): ?>
<section class="sheet">
  <header class="sheet-head">
    <div class="group-badge"><?= h(t('Gruppe %s', $card['group']['name'])) ?></div>
    <div class="sheet-title">
      <div class="sheet-eyebrow">Badi Boccia Buochs</div>
      <h1><?= h($season['label']) ?></h1>
    </div>
    <svg class="balls" viewBox="0 0 120 70" aria-hidden="true">
      <defs>
        <radialGradient id="shine" cx="35%" cy="30%" r="70%">
          <stop offset="0" stop-color="#fff" stop-opacity=".75"/>
          <stop offset=".45" stop-color="#fff" stop-opacity="0"/>
          <stop offset="1" stop-color="#000" stop-opacity=".25"/>
        </radialGradient>
      </defs>
      <ellipse cx="62" cy="64" rx="56" ry="5" fill="#000" opacity=".08"/>
      <circle cx="22" cy="44" r="18" fill="#d6312b"/><circle cx="22" cy="44" r="18" fill="url(#shine)"/>
      <circle cx="58" cy="26" r="18" fill="#2f6fd6"/><circle cx="58" cy="26" r="18" fill="url(#shine)"/>
      <circle cx="96" cy="44" r="18" fill="#1f9a4c"/><circle cx="96" cy="44" r="18" fill="url(#shine)"/>
      <circle cx="60" cy="56" r="8" fill="#f4f0e6" stroke="#c9c2b0"/><circle cx="60" cy="56" r="8" fill="url(#shine)"/>
    </svg>
  </header>

  <div class="sheet-intro">
    <ul class="team-list">
      <li class="team-list-head"><span></span><span><?= h(t('Punkte')) ?></span></li>
      <?php foreach ($card['teams'] as $team): ?>
        <li><span class="team-box" style="--team: <?= h($team['color_hex']) ?>"><?= h($team['name']) ?></span><span class="tally"></span></li>
      <?php endforeach; ?>
    </ul>
    <ul class="rules">
      <li><?= h(t('Ein Spiel geht auf 15 Punkte. Gespielt wird auf 2 Gewinnsätze.')) ?></li>
      <li class="rule-points"><?= h(t('2:0-Sieger = 3 Punkte · 2:1-Sieger = 2 Punkte · 2:1-Verlierer = 1 Punkt')) ?></li>
      <li><?= h($qualificationRule) ?></li>
      <li><?= h(t('Die Spieltermine müssen untereinander ausgemacht werden.')) ?></li>
      <li><?= t('Die Gruppenspiele müssen bis %s ausgetragen sein.', '<strong class="deadline">' . h($defaultDeadline) . '</strong>') ?></li>
      <li><?= h(t('Die K.-o.-Spiele werden auf 21 Punkte gespielt.')) ?></li>
    </ul>
  </div>

  <div class="games">
    <div class="games-head">
      <span><?= h(t('Team')) ?></span>
      <span></span>
      <span><?= h(t('Gegner')) ?></span>
      <span><?= h(t('Spieldatum / Zeit')) ?></span>
      <span><?= h(t('Resultat')) ?></span>
    </div>
    <?php foreach ($card['pairings'] as $i => [$teamA, $teamB]): ?>
      <div class="game-row">
        <span class="team-box" style="--team: <?= h($teamA['color_hex']) ?>"><?= h($teamA['name']) ?></span>
        <span class="game-no"><?= h(t('Spiel %d', $i + 1)) ?></span>
        <span class="team-box" style="--team: <?= h($teamB['color_hex']) ?>"><?= h($teamB['name']) ?></span>
        <span class="write-box"></span>
        <span class="write-box result-box">:</span>
      </div>
    <?php endforeach; ?>
  </div>

  <footer class="sheet-foot">
    <?= h(t('Resultate und Termine auch online eintragen:')) ?> <strong><?= h($siteUrl) ?></strong>
  </footer>
</section>
<?php endforeach; ?>

<script>
(function () {
  var input = document.getElementById('deadline-input');
  input.addEventListener('input', function () {
    document.querySelectorAll('.deadline').forEach(function (el) { el.textContent = input.value; });
  });
})();
</script>
</body>
</html>
