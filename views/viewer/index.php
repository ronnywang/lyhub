<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>立委帳號列表 — <?= $this->escape($this->domain) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: sans-serif; background: #f5f5f5; color: #222; }
header { background: #2c3e50; color: #fff; padding: 16px 24px; }
header h1 { font-size: 1.2rem; }
header p { font-size: 0.85rem; opacity: 0.7; margin-top: 4px; }
.container { max-width: 1200px; margin: 24px auto; padding: 0 16px; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); text-decoration: none; color: inherit; display: flex; flex-direction: column; transition: box-shadow .15s; }
.card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.18); }
.card img { width: 100%; aspect-ratio: 1; object-fit: cover; background: #ddd; }
.card-body { padding: 8px 10px 10px; flex: 1; }
.card-name { font-weight: bold; font-size: 1rem; }
.card-party { font-size: 0.78rem; margin-top: 3px; padding: 2px 6px; border-radius: 4px; display: inline-block; }
.card-dist { font-size: 0.75rem; color: #666; margin-top: 4px; }
.party-kmt { background: #2671d9; color: #fff; }
.party-dpp { background: #1b9431; color: #fff; }
.party-tpp { background: #28b8c6; color: #fff; }
.party-other { background: #888; color: #fff; }
.section-title { font-size: 1rem; font-weight: bold; color: #555; margin: 20px 0 10px; border-bottom: 2px solid #e0e0e0; padding-bottom: 6px; }
</style>
</head>
<body>
<header>
  <h1>🏛 立委帳號列表</h1>
  <p><?= $this->escape($this->domain) ?> · 第11屆立法委員</p>
</header>
<div class="container">
<?php
$parties = [];
foreach ($this->legislators as $mp) {
  $parties[$mp->黨籍][] = $mp;
}
$party_names = ['中國國民黨', '民主進步黨', '台灣民眾黨'];
foreach ($parties as $k => $_) {
  if (!in_array($k, $party_names)) $party_names[] = $k;
}
foreach ($party_names as $party) {
  if (empty($parties[$party])) continue;
  $party_css = match($party) {
    '中國國民黨' => 'kmt',
    '民主進步黨' => 'dpp',
    '台灣民眾黨' => 'tpp',
    default => 'other',
  };
?>
  <div class="section-title"><?= $this->escape($party) ?>（<?= count($parties[$party]) ?> 人）</div>
  <div class="grid">
<?php foreach ($parties[$party] as $mp) { ?>
<?php $account = 'mp-' . $mp->歷屆立法委員編號; ?>
    <a class="card" href="/viewer/<?= urlencode($account) ?>">
      <img src="<?= $this->escape($mp->照片位址 ?? '') ?>" alt="<?= $this->escape($mp->委員姓名) ?>" loading="lazy">
      <div class="card-body">
        <div class="card-name"><?= $this->escape($mp->委員姓名) ?></div>
        <span class="card-party party-<?= $this->escape($party_css) ?>"><?= $this->escape($party) ?></span>
        <div class="card-dist"><?= $this->escape($mp->選區名稱 ?? '') ?></div>
      </div>
    </a>
<?php } ?>
  </div>
<?php } ?>
</div>
</body>
</html>
