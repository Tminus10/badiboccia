<?php
/** @var array $targetTeam
 * @var string|null $error
 */
$pageTitle = 'PIN eingeben';
$returnTo = $_GET['return_to'] ?? ('/team/' . $targetTeam['id']);
?>
<div class="pin-page">
  <a class="back-link" href="<?= h(url('/login')) ?>">← Anderes Team wählen</a>
  <?= render_partial('partials/team-avatar', ['team' => $targetTeam, 'size' => 'lg', 'linked' => false]) ?>
  <h1><?= h($targetTeam['name']) ?></h1>
  <p class="muted">Gib euren 4-stelligen PIN ein.</p>

  <?php if ($error === 'wrong'): ?>
    <p class="form-error">Falscher PIN. Bitte nochmals versuchen.</p>
  <?php elseif ($error === 'locked'): ?>
    <p class="form-error">Zu viele Versuche. Bitte in 15 Minuten erneut versuchen, oder wendet euch an einen Admin.</p>
  <?php endif; ?>

  <form method="post" action="<?= h(url('/login/team/' . $targetTeam['id'])) ?>" class="pin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
    <input
      type="text"
      name="pin"
      inputmode="numeric"
      pattern="[0-9]{4}"
      maxlength="4"
      autocomplete="off"
      autofocus
      required
      class="pin-input"
      aria-label="4-stelliger PIN">
    <button class="btn btn-primary btn-lg" type="submit">Anmelden</button>
  </form>
</div>
