<?php
/** @var array $admin
 * @var array $season
 * @var array[] $entries
 * @var array[] $teamsById
 * @var array[] $gamesById
 */
$pageTitle = t('Änderungsprotokoll') . ' – ' . $season['label'];
?>
<div class="page-head">
  <div>
    <p class="eyebrow"><a href="<?= h(url('/admin/season/' . $season['id'])) ?>"><?= h($season['label']) ?></a></p>
    <h1><?= h(t('Änderungsprotokoll')) ?></h1>
  </div>
</div>

<?php if (empty($entries)): ?>
  <p class="empty-state"><?= h(t('Noch keine Änderungen erfasst.')) ?></p>
<?php else: ?>
  <div class="table-scroll">
    <table class="audit-table">
      <thead>
        <tr><th><?= h(t('Zeit')) ?></th><th><?= h(t('Spiel')) ?></th><th><?= h(t('Von')) ?></th><th><?= h(t('Alt')) ?></th><th><?= h(t('Neu')) ?></th></tr>
      </thead>
      <tbody>
        <?php foreach ($entries as $entry): ?>
          <?php
            $game = $gamesById[$entry['game_id']] ?? null;
            $teamA = $game && $game['team_a_id'] ? ($teamsById[$game['team_a_id']] ?? null) : null;
            $teamB = $game && $game['team_b_id'] ? ($teamsById[$game['team_b_id']] ?? null) : null;
            $old = $entry['old_sets_a'] === null ? '–' : $entry['old_sets_a'] . ':' . $entry['old_sets_b'] . ($entry['old_played_date'] ? ' (' . format_date_ch($entry['old_played_date']) . ')' : '');
            $new = $entry['new_sets_a'] . ':' . $entry['new_sets_b'] . ($entry['new_played_date'] ? ' (' . format_date_ch($entry['new_played_date']) . ')' : '');
          ?>
          <tr>
            <td class="nowrap"><?= h(date('d.m.Y H:i', strtotime($entry['changed_at']))) ?></td>
            <td><?= $teamA ? h($teamA['name']) : '?' ?> – <?= $teamB ? h($teamB['name']) : '?' ?></td>
            <td><?= h($entry['actor_label'] ?? '') ?> <span class="muted">(<?= h($entry['actor_type']) ?>)</span></td>
            <td class="muted"><?= h($old) ?></td>
            <td><?= h($new) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
