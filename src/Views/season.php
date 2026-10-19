<?php
/** @var array $season
 * @var array[] $groupData
 * @var int $qualifiers
 * @var array|null $viewerTeam
 * @var array|null $admin
 */
$isCurrent = (int) $season['is_current'] === 1;
$pageTitle = $season['label'];
?>
<div class="page-head">
  <div>
    <h1><?= h($season['label']) ?></h1>
    <?php if (!$isCurrent): ?><span class="badge badge-archive"><?= h(t('Archiviert')) ?></span><?php endif; ?>
  </div>
  <a class="btn btn-secondary" href="<?= h(url($isCurrent ? '/bracket' : '/bracket/' . $season['id'])) ?>"><?= h(t('Turnierbaum ansehen')) ?> →</a>
</div>

<?php if (empty($groupData)): ?>
  <p class="empty-state"><?= h(t('Für diese Saison sind noch keine Gruppen angelegt.')) ?></p>
<?php endif; ?>

<div class="group-grid">
  <?php foreach ($groupData as $entry): ?>
    <?php $group = $entry['group']; $standings = $entry['standings']; ?>
    <section class="card group-card">
      <header class="group-card-head">
        <h2><?= h(t('Gruppe %s', $group['name'])) ?></h2>
        <span class="muted"><?= h(t('%1$d/%2$d Spiele', (int) $entry['gamesPlayed'], (int) $entry['gamesTotal'])) ?></span>
      </header>
      <?php if (empty($standings)): ?>
        <p class="empty-state small"><?= h(t('Noch keine Teams.')) ?></p>
      <?php else: ?>
        <table class="standings-table standings-compact">
          <thead>
            <tr><th class="rank-col">#</th><th><?= h(t('Team')) ?></th><th><?= h(t('Sp')) ?></th><th><?= h(t('Pkt')) ?></th></tr>
          </thead>
          <tbody>
            <?php foreach ($standings as $row): ?>
              <tr class="<?= ($row['rank'] <= $qualifiers && $entry['gamesPlayed'] > 0) ? 'qualifies' : '' ?>">
                <td class="rank-col"><?= (int) $row['rank'] ?></td>
                <td><?= render_partial('partials/team-dot', ['team' => $row['team'], 'seasonId' => (int) $season['id']]) ?></td>
                <td><?= (int) $row['played'] ?></td>
                <td class="pts"><?= (int) $row['points'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <a class="card-link" href="<?= h(url('/group/' . $group['id'])) ?>"><?= h(t('Alle Spiele & Resultate')) ?> →</a>
    </section>
  <?php endforeach; ?>
</div>
