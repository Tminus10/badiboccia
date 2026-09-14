<?php
/** @var array $team
 * @var string|null $size 'sm'|'md'|'lg'
 * @var bool|null $linked
 * @var int|string|null $seasonId season to scope the link to (int), 'all' for full history, or omit for the team's current season
 */
$size = $size ?? 'md';
$linked = $linked ?? true;
$seasonId = $seasonId ?? null;
$photoUrl = team_photo_url($team);
$avatarHtml = '<span class="avatar avatar-' . h($size) . '" style="--team-color: ' . h($team['color_hex']) . '">'
    . ($photoUrl
        ? '<img src="' . h($photoUrl) . '" alt="" loading="lazy">'
        : '<span class="avatar-fallback">' . h(initials($team['name'])) . '</span>')
    . '</span>';
?>
<?php if ($linked): ?>
  <a class="avatar-link" href="<?= h(team_url((int) $team['id'], $seasonId)) ?>"><?= $avatarHtml ?></a>
<?php else: ?>
  <?= $avatarHtml ?>
<?php endif; ?>
