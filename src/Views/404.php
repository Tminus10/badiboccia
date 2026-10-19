<?php $pageTitle = t('Nicht gefunden'); ?>
<div class="empty-hero">
  <h1><?= h(t('Seite nicht gefunden')) ?></h1>
  <p><?= h(t('Diese Seite existiert nicht (mehr).')) ?></p>
  <a class="btn btn-primary" href="<?= h(url('/')) ?>"><?= h(t('Zur Übersicht')) ?></a>
</div>
