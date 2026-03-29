<?php
include(__DIR__ . '/../init.inc.php');

$domain = getenv('APP_DOMAIN');
if (!$domain) {
    echo "Error: APP_DOMAIN 未設定，請在 config.inc.php 加入 putenv('APP_DOMAIN=...')\n";
    exit(1);
}

// LYDataHelper 和 formatActivityObject 內部會用到 $_SERVER['HTTP_HOST']
$_SERVER['HTTP_HOST'] = $domain;

// 取得所有有在追蹤中的帳號清單
$active_followers = Follower::search(['status' => 1])->toArray();
$accounts = array_unique(array_column($active_followers, 'account'));

if (empty($accounts)) {
    echo "目前沒有任何追蹤者，不需推播。\n";
    exit(0);
}

echo "共 " . count($accounts) . " 個帳號有追蹤者，開始檢查新動態...\n\n";

foreach ($accounts as $account) {
    if (!preg_match('#^mp-(\d+)$#', $account, $matches)) continue;

    $mp_id = $matches[1];
    echo "[{$account}] 取得資料中...";
    $mp_data = LYDataHelper::getMPData($mp_id);
    $records = LYDataHelper::getMPRecords($mp_data);
    echo " 共 " . count($records) . " 筆\n";

    // 取得此帳號所有追蹤者的 inbox（去重）
    $account_followers = Follower::search(['account' => $account, 'status' => 1])->toArray();
    $inboxes = array_unique(array_filter(array_column($account_followers, 'inbox')));

    $pushed_count = 0;
    foreach ($records as $record) {
        [$type, $timestamp, $data] = $record;
        $activity_key = $type . '-' . ($type === 'ivod' ? $data->IVOD_ID : $data->議案編號);

        if (PushedActivity::isPushed($account, $activity_key)) {
            continue;
        }

        // 組 Create activity（加上 @context 才是完整的 ActivityPub 訊息）
        $activity = LYDataHelper::formatActivityObject($account, $record);
        $activity['@context'] = [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
        ];
        $body = json_encode($activity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        foreach ($inboxes as $inbox) {
            ActivityPubHelper::send_signed_request($inbox, $body, $account, $domain);
        }

        PushedActivity::markPushed($account, $activity_key);
        $pushed_count++;
        echo "  ✓ 推播 {$activity_key} → " . count($inboxes) . " 個 inbox\n";
    }

    if ($pushed_count === 0) {
        echo "  （無新動態）\n";
    }
    echo "\n";
}

echo "完成。\n";
