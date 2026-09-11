<?php
/** @var array|null $season
 * @var array[] $groupData
 * @var string|null $error
 */
$pageTitle = 'Team-Login';
?>
<div class="page-head"><div><h1>Team-Login</h1><p class="muted">Wähle euer Team, um ein Resultat einzutragen.</p></div></div>

<?php if ($season === null): ?>
  <p class="empty-state">Es ist keine aktive Saison eingerichtet.</p>
<?php else: ?>
  <?php foreach ($groupData as $entry): ?>
    <section class="login-group">
      <h2>Gruppe <?= h($entry['group']['name']) ?></h2>
      <?php if (empty($entry['teams'])): ?>
        <p class="empty-state small">Keine Teams.</p>
      <?php else: ?>
        <div class="team-pick-grid">
          <?php foreach ($entry['teams'] as $team): ?>
            <a class="team-pick" href="<?= h(url('/login/team/' . $team['id'])) ?>" style="--team-color: <?= h($team['color_hex']) ?>">
              <?= render_partial('partials/team-avatar', ['team' => $team, 'size' => 'sm', 'linked' => false]) ?>
              <span><?= h($team['name']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
<?php endif; ?>
