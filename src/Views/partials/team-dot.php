<?php
/** @var array $team
 * @var int|string|null $seasonId season to scope the link to (int), 'all' for full history, or omit for the team's current season
 */
$seasonId = $seasonId ?? null;
?>
<a class="team-link" href="<?= h(team_url((int) $team['id'], $seasonId)) ?>">
  <span class="dot" style="--dot: <?= h($team['color_hex']) ?>" aria-hidden="true"></span>
  <span class="team-name"><?= h($team['name']) ?></span>
</a>
