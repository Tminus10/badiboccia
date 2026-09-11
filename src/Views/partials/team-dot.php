<?php
/** @var array $team */
?>
<a class="team-link" href="<?= h(url('/team/' . $team['id'])) ?>">
  <span class="dot" style="--dot: <?= h($team['color_hex']) ?>" aria-hidden="true"></span>
  <span class="team-name"><?= h($team['name']) ?></span>
</a>
