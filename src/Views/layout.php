<?php
$currentTeam = TeamAuth::current();
$currentAdmin = AdminAuth::current();
$flashes = flash_take();
?><!doctype html>
<html lang="de-CH">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="theme-color" content="#0a7d6c">
<title><?= is_dev_env() ? '[DEV] ' : '' ?><?= isset($pageTitle) ? h($pageTitle) . ' – Badi Boccia Buochs' : 'Badi Boccia Buochs' ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎯</text></svg>">
<link rel="stylesheet" href="<?= h(asset_url('/assets/css/style.css')) ?>">
</head>
<body>
<?php if (is_dev_env()): ?><div class="dev-banner">🧪 DEV-Umgebung &ndash; nicht die Live-Website</div><?php endif; ?>
<header class="topbar">
  <div class="topbar-inner">
    <div class="topbar-row">
      <a class="brand" href="<?= h(url('/')) ?>">
        <span class="brand-mark" aria-hidden="true"></span>
        Badi Boccia Buochs
      </a>
      <div class="account">
        <?php if ($currentAdmin): ?>
          <span class="pill pill-admin">Admin: <?= h($currentAdmin['display_name']) ?></span>
          <a class="btn btn-ghost btn-sm" href="<?= h(url('/admin')) ?>">Admin-Bereich</a>
          <a class="btn btn-ghost btn-sm" href="<?= h(url('/admin/teams')) ?>">Teams</a>
          <form method="post" action="<?= h(url('/logout')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="<?= h($_SERVER['REQUEST_URI']) ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Abmelden</button>
          </form>
        <?php elseif ($currentTeam): ?>
          <a class="pill team-pill" href="<?= h(url('/team/' . $currentTeam['id'])) ?>" style="--dot: <?= h($currentTeam['color_hex']) ?>">
            <span class="dot" aria-hidden="true"></span><?= h($currentTeam['name']) ?>
          </a>
          <form method="post" action="<?= h(url('/logout')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="<?= h($_SERVER['REQUEST_URI']) ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Abmelden</button>
          </form>
        <?php else: ?>
          <a class="btn btn-primary btn-sm" href="<?= h(url('/login')) ?>">Anmelden</a>
        <?php endif; ?>
      </div>
    </div>
    <nav class="topnav">
      <a href="<?= h(url('/')) ?>">Übersicht</a>
      <a href="<?= h(url('/bracket')) ?>">Turnierbaum</a>
      <a href="<?= h(url('/calendar')) ?>">Kalender</a>
      <a href="<?= h(url('/archive')) ?>">Archiv</a>
      <a href="<?= h(url('/regeln')) ?>">Regeln</a>
    </nav>
  </div>
</header>

<?php if ($flashes): ?>
  <div class="flash-stack">
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<main class="page">
<?= $content ?>
</main>

<footer class="site-footer">
  <p>Badi Boccia Buochs &middot; <a href="<?= h(url('/archive')) ?>">Archiv</a> &middot; <?php if (!$currentAdmin): ?><a href="<?= h(url('/admin/login')) ?>">Admin-Login</a><?php endif; ?></p>
</footer>
<script>
(function () {
  // Auto-inserts the "." / ":" separators as the admin types into a result-form date or
  // time field, so entry always reads in Swiss format regardless of the browser's own
  // language -- a plain <input type="date"/"time"> displays in whatever format the browser's
  // UI language uses, which isn't controllable from the page and isn't always Swiss.
  document.addEventListener('input', function (e) {
    var el = e.target;
    if (el.matches && el.matches('.date-text-input')) {
      var digits = el.value.replace(/\D/g, '').slice(0, 8);
      el.value = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)].filter(Boolean).join('.');
    } else if (el.matches && el.matches('.time-text-input')) {
      var digits = el.value.replace(/\D/g, '').slice(0, 4);
      el.value = [digits.slice(0, 2), digits.slice(2, 4)].filter(Boolean).join(':');
    }
  });
})();
</script>
</body>
</html>
