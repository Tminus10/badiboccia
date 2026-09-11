<?php
/** @var array $team
 * @var string|null $size 'sm'|'md'|'lg'
 * @var bool|null $linked
 */
$size = $size ?? 'md';
$linked = $linked ?? true;
$photoUrl = team_photo_url($team);
$avatarHtml = '<span class="avatar avatar-' . h($size) . '" style="--team-color: ' . h($team['color_hex']) . '">'
    . ($photoUrl
        ? '<img src="' . h($photoUrl) . '" alt="" loading="lazy">'
        : '<span class="avatar-fallback">' . h(initials($team['name'])) . '</span>')
    . '</span>';
?>
<?php if ($linked): ?>
  <a class="avatar-link" href="<?= h(url('/team/' . $team['id'])) ?>"><?= $avatarHtml ?></a>
<?php else: ?>
  <?= $avatarHtml ?>
<?php endif; ?>
