<?php $pageTitle = t('Willkommen'); ?>
<div class="empty-hero">
  <h1><?= h(t('Willkommen bei Badi Boccia Buochs')) ?></h1>
  <p><?= h(t('Es ist noch keine aktive Saison eingerichtet.')) ?></p>
  <a class="btn btn-primary" href="<?= h(url('/admin/login')) ?>"><?= h(t('Als Admin anmelden & Saison anlegen')) ?></a>
</div>
