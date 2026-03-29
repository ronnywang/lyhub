<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $this->escape($this->mp_data->委員姓名) ?> — <?= $this->escape($this->domain) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: sans-serif; background: #f5f5f5; color: #222; }
header { background: #2c3e50; color: #fff; padding: 16px 24px; display: flex; align-items: center; gap: 12px; }
header a { color: #aac; text-decoration: none; font-size: 0.9rem; }
header a:hover { color: #fff; }
.container { max-width: 800px; margin: 24px auto; padding: 0 16px; }
.profile { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.1); display: flex; gap: 20px; align-items: flex-start; margin-bottom: 20px; }
.profile img { width: 100px; height: 100px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.profile-info h2 { font-size: 1.4rem; }
.profile-info .handle { color: #888; font-size: 0.9rem; margin-top: 4px; }
.profile-info .summary { margin-top: 8px; font-size: 0.9rem; white-space: pre-line; color: #444; }
.profile-links { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
.profile-links a { font-size: 0.8rem; background: #eee; border-radius: 4px; padding: 3px 8px; text-decoration: none; color: #555; }
.profile-links a:hover { background: #ddd; }
.section-title { font-size: 1rem; font-weight: bold; color: #555; margin: 0 0 12px; border-bottom: 2px solid #e0e0e0; padding-bottom: 6px; }
.activity-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
.activity { background: #fff; border-radius: 8px; padding: 14px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.activity-meta { font-size: 0.78rem; color: #888; margin-bottom: 8px; display: flex; gap: 10px; align-items: center; }
.activity-type { font-weight: bold; padding: 1px 6px; border-radius: 3px; font-size: 0.75rem; }
.type-bill-first { background: #d4edda; color: #155724; }
.type-bill-second { background: #cce5ff; color: #004085; }
.type-ivod { background: #fff3cd; color: #856404; }
.activity-content { font-size: 0.88rem; line-height: 1.6; color: #333; }
.activity-content p { margin-bottom: 4px; }
.activity-content a { color: #2c6fad; }
.activity-content .invisible { display: none; }
.activity-content .ellipsis { display: inline; }
.empty { color: #888; font-size: 0.9rem; padding: 20px 0; text-align: center; }
</style>
</head>
<body>
<header>
  <a href="/viewer">← 回列表</a>
</header>
<div class="container">
  <div class="profile">
    <img src="<?= $this->escape($this->mp_data->照片位址 ?? '') ?>" alt="<?= $this->escape($this->mp_data->委員姓名) ?>">
    <div class="profile-info">
      <h2><?= $this->escape($this->mp_data->委員姓名) ?></h2>
      <div class="handle">@<?= $this->escape($this->account) ?>@<?= $this->escape($this->domain) ?> · 追蹤者 <?= intval($this->follower_count) ?> 人</div>
      <div class="summary"><?= $this->escape(sprintf(
        "第%02d屆立法委員\n黨籍：%s　選區：%s\n委員會：%s",
        $this->mp_data->屆,
        $this->mp_data->黨籍,
        $this->mp_data->選區名稱,
        $this->mp_data->委員會[count($this->mp_data->委員會) - 1] ?? ''
      )) ?></div>
      <div class="profile-links">
        <a href="<?= $this->escape($this->actor_url) ?>" target="_blank">Actor JSON</a>
        <a href="<?= $this->escape($this->outbox_url) ?>" target="_blank">Outbox JSON</a>
        <a href="<?= $this->escape($this->followers_url) ?>" target="_blank">Followers JSON</a>
      </div>
    </div>
  </div>

  <div class="section-title">最新動態（最多 20 筆）</div>
  <div class="activity-list">
<?php if (empty($this->records)) { ?>
    <div class="empty">目前沒有動態資料</div>
<?php } ?>
<?php foreach ($this->records as $record) { ?>
<?php
  [$type, $timestamp, $data] = $record;
  $type_label = match($type) {
    'bill-first' => '提案',
    'bill-second' => '連署',
    'ivod' => '發言',
    default => $type,
  };
  $date_str = date('Y-m-d', $timestamp);
  $activity = LYDataHelper::formatActivityObject($this->account, $record);
  $content = $activity['object']['content'] ?? '';
?>
    <div class="activity">
      <div class="activity-meta">
        <span class="activity-type type-<?= $this->escape($type) ?>"><?= $this->escape($type_label) ?></span>
        <span><?= $this->escape($date_str) ?></span>
      </div>
      <div class="activity-content"><?= $content ?></div>
    </div>
<?php } ?>
  </div>
</div>
</body>
</html>
