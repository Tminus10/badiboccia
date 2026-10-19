<?php
/** @var array[] $seasons */
$pageTitle = t('Archiv');
?>
<div class="page-head"><div><h1><?= h(t('Archiv')) ?></h1><p class="muted"><?= h(t('Vergangene Saisons zum Nachschauen.')) ?></p></div></div>

<?php if (empty($seasons)): ?>
  <p class="empty-state"><?= h(t('Noch keine archivierten Saisons vorhanden.')) ?></p>
<?php else: ?>
  <ul class="season-list">
    <?php foreach ($seasons as $season): ?>
      <li class="card season-list-item">
        <a href="<?= h(url('/bracket/' . $season['id'])) ?>">
          <strong><?= h($season['label']) ?></strong>
          <span class="muted"><?= (int) $season['year'] ?></span>
        </a>
        <a class="card-link" href="<?= h(url('/calendar/' . $season['id'])) ?>"><?= h(t('Kalender')) ?> →</a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
