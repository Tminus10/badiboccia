<?php
/** @var string|null $error */
$pageTitle = t('Admin-Login');
?>
<div class="pin-page">
  <h1><?= h(t('Admin-Login')) ?></h1>
  <?php if ($error): ?>
    <p class="form-error"><?= h(t('Benutzername oder Passwort falsch.')) ?></p>
  <?php endif; ?>
  <form method="post" action="<?= h(url('/admin/login')) ?>" class="admin-login-form">
    <?= csrf_field() ?>
    <label><?= h(t('Benutzername')) ?>
      <input type="text" name="username" autocomplete="username" required autofocus>
    </label>
    <label><?= h(t('Passwort')) ?>
      <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button class="btn btn-primary btn-lg" type="submit"><?= h(t('Anmelden')) ?></button>
  </form>
</div>
