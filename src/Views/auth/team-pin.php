<?php
/** @var array $targetTeam
 * @var string|null $error
 */
$pageTitle = t('PIN eingeben');
$returnTo = $_GET['return_to'] ?? ('/team/' . $targetTeam['id']);
?>
<div class="pin-page">
  <a class="back-link" href="<?= h(url('/login')) ?>">← <?= h(t('Anderes Team wählen')) ?></a>
  <?= render_partial('partials/team-avatar', ['team' => $targetTeam, 'size' => 'lg', 'linked' => false]) ?>
  <h1><?= h($targetTeam['name']) ?></h1>
  <p class="muted"><?= h(t('Gib euren 4-stelligen PIN ein.')) ?></p>

  <?php if ($error === 'wrong'): ?>
    <p class="form-error"><?= h(t('Falscher PIN. Bitte versuche es nochmals.')) ?></p>
  <?php elseif ($error === 'locked'): ?>
    <p class="form-error"><?= h(t('Zu viele Fehlversuche. Bitte versuche es in 15 Minuten erneut oder wende dich an einen Admin.')) ?></p>
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
      aria-label="<?= h(t('4-stelliger PIN')) ?>">
    <button class="btn btn-primary btn-lg" type="submit"><?= h(t('Anmelden')) ?></button>
  </form>
</div>
