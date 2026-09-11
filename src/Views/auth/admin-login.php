<?php
/** @var string|null $error */
$pageTitle = 'Admin-Login';
?>
<div class="pin-page">
  <h1>Admin-Login</h1>
  <?php if ($error): ?>
    <p class="form-error">Benutzername oder Passwort falsch.</p>
  <?php endif; ?>
  <form method="post" action="<?= h(url('/admin/login')) ?>" class="admin-login-form">
    <?= csrf_field() ?>
    <label>Benutzername
      <input type="text" name="username" autocomplete="username" required autofocus>
    </label>
    <label>Passwort
      <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button class="btn btn-primary btn-lg" type="submit">Anmelden</button>
  </form>
</div>
