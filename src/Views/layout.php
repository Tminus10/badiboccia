<?php
$currentTeam = TeamAuth::current();
$currentAdmin = AdminAuth::current();
$flashes = flash_take();
?><!doctype html>
<html lang="<?= h(I18n::htmlLang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="theme-color" content="#0a7d6c">
<title><?= is_dev_env() ? '[DEV] ' : '' ?><?= isset($pageTitle) ? h($pageTitle) . ' – Badi Boccia Buochs' : 'Badi Boccia Buochs' ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎯</text></svg>">
<link rel="stylesheet" href="<?= h(asset_url('/assets/css/style.css')) ?>">
</head>
<body>
<?php if (is_dev_env()): ?><div class="dev-banner">🧪 <?= h(t('DEV-Umgebung – nicht die Live-Website')) ?></div><?php endif; ?>
<header class="topbar">
  <div class="topbar-inner">
    <div class="topbar-row">
      <a class="brand" href="<?= h(url('/')) ?>">
        <span class="brand-mark" aria-hidden="true"></span>
        Badi Boccia Buochs
      </a>
      <div class="account">
        <?php if ($currentAdmin): ?>
          <span class="pill pill-admin"><?= h(t('Admin: %s', $currentAdmin['display_name'])) ?></span>
          <a class="btn btn-ghost btn-sm" href="<?= h(url('/admin')) ?>"><?= h(t('Admin-Bereich')) ?></a>
          <a class="btn btn-ghost btn-sm" href="<?= h(url('/admin/teams')) ?>"><?= h(t('Teams')) ?></a>
          <form method="post" action="<?= h(url('/logout')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="<?= h($_SERVER['REQUEST_URI']) ?>">
            <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('Abmelden')) ?></button>
          </form>
        <?php elseif ($currentTeam): ?>
          <a class="pill team-pill" href="<?= h(url('/team/' . $currentTeam['id'])) ?>" style="--dot: <?= h($currentTeam['color_hex']) ?>">
            <span class="dot" aria-hidden="true"></span><?= h($currentTeam['name']) ?>
          </a>
          <form method="post" action="<?= h(url('/logout')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="<?= h($_SERVER['REQUEST_URI']) ?>">
            <button class="btn btn-ghost btn-sm" type="submit"><?= h(t('Abmelden')) ?></button>
          </form>
        <?php else: ?>
          <a class="btn btn-primary btn-sm" href="<?= h(url('/login')) ?>"><?= h(t('Anmelden')) ?></a>
        <?php endif; ?>
      </div>
    </div>
    <nav class="topnav">
      <a href="<?= h(url('/')) ?>"><?= h(t('Übersicht')) ?></a>
      <a href="<?= h(url('/bracket')) ?>"><?= h(t('Turnierbaum')) ?></a>
      <a href="<?= h(url('/calendar')) ?>"><?= h(t('Kalender')) ?></a>
      <a href="<?= h(url('/archive')) ?>"><?= h(t('Archiv')) ?></a>
      <a href="<?= h(url('/regeln')) ?>"><?= h(t('Regeln')) ?></a>
      <?php foreach (I18n::LANGUAGES as $code => [$languageName]): ?>
        <?php if ($code !== I18n::locale()): ?><a class="topnav-lang" href="<?= h(lang_url($code)) ?>" hreflang="<?= h($code) ?>" lang="<?= h($code) ?>"><?= h($languageName) ?></a><?php endif; ?>
      <?php endforeach; ?>
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
  <p>Badi Boccia Buochs &middot; <a href="<?= h(url('/archive')) ?>"><?= h(t('Archiv')) ?></a> &middot; <?php if (!$currentAdmin): ?><a href="<?= h(url('/admin/login')) ?>"><?= h(t('Admin-Login')) ?></a> &middot; <?php endif; ?>
    <span class="lang-switch">
      <?php foreach (I18n::LANGUAGES as $code => [$languageName]): ?>
        <?php if ($code === I18n::locale()): ?><strong><?= h($languageName) ?></strong><?php else: ?><a href="<?= h(lang_url($code)) ?>" hreflang="<?= h($code) ?>" lang="<?= h($code) ?>"><?= h($languageName) ?></a><?php endif; ?>
      <?php endforeach; ?>
    </span>
  </p>
</footer>
</body>
</html>
