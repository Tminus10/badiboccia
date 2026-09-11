<?php
/** @var array $season
 * @var array[] $groupData
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = $season['label'];
?>
<div class="page-head">
  <div>
    <h1><?= h($season['label']) ?></h1>
    <?php if (!$isCurrent): ?><span class="badge badge-archive">Archiviert</span><?php endif; ?>
  </div>
  <a class="btn btn-secondary" href="<?= h(url($isCurrent ? '/bracket' : '/bracket/' . $season['id'])) ?>">Turnierbaum ansehen →</a>
</div>

<?php if (empty($groupData)): ?>
  <p class="empty-state">Für diese Saison sind noch keine Gruppen angelegt.</p>
<?php endif; ?>

<div class="group-grid">
  <?php foreach ($groupData as $entry): ?>
    <?php $group = $entry['group']; $standings = $entry['standings']; ?>
    <section class="card group-card">
      <header class="group-card-head">
        <h2>Gruppe <?= h($group['name']) ?></h2>
        <span class="muted"><?= (int) $entry['gamesPlayed'] ?>/<?= (int) $entry['gamesTotal'] ?> Spiele</span>
      </header>
      <?php if (empty($standings)): ?>
        <p class="empty-state small">Noch keine Teams.</p>
      <?php else: ?>
        <table class="standings-table standings-compact">
          <thead>
            <tr><th class="rank-col">#</th><th>Team</th><th>Sp</th><th>Pkt</th></tr>
          </thead>
          <tbody>
            <?php foreach ($standings as $row): ?>
              <tr class="<?= $row['rank'] <= 2 ? 'qualifies' : '' ?>">
                <td class="rank-col"><?= (int) $row['rank'] ?></td>
                <td><?= render_partial('partials/team-dot', ['team' => $row['team']]) ?></td>
                <td><?= (int) $row['played'] ?></td>
                <td class="pts"><?= (int) $row['points'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <a class="card-link" href="<?= h(url('/group/' . $group['id'])) ?>">Alle Spiele & Resultate →</a>
    </section>
  <?php endforeach; ?>
</div>
