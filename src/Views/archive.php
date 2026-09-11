<?php
/** @var array[] $seasons */
$pageTitle = 'Archiv';
?>
<div class="page-head"><div><h1>Archiv</h1><p class="muted">Vergangene Saisons zum Nachschauen.</p></div></div>

<?php if (empty($seasons)): ?>
  <p class="empty-state">Noch keine archivierten Saisons vorhanden.</p>
<?php else: ?>
  <ul class="season-list">
    <?php foreach ($seasons as $season): ?>
      <li class="card season-list-item">
        <a href="<?= h(url('/season/' . $season['id'])) ?>">
          <strong><?= h($season['label']) ?></strong>
          <span class="muted"><?= (int) $season['year'] ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
