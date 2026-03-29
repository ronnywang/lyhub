<?php
include(__DIR__ . '/../init.inc.php');

$test_account = 'mp-1292';
$test_follower = 'https://test.example.com/users/testuser';
$test_inbox = 'https://test.example.com/inbox';

function check($label, $condition) {
    echo ($condition ? '✓' : '✗') . " {$label}\n";
}

// 確保乾淨的起始狀態
$existing = Follower::search(['account' => $test_account, 'follower' => $test_follower])->first();
if ($existing) {
    $existing->delete();
    echo "(已清除舊測試資料)\n";
}
echo "\n";

// 測試 1：追蹤
Follower::follow($test_account, $test_follower, $test_inbox, true);
$r = Follower::search(['account' => $test_account, 'follower' => $test_follower])->first();
check('追蹤後記錄存在', $r !== null);
check('status = 1', ($r->status ?? -1) == 1);
check('inbox 正確儲存', ($r->inbox ?? '') == $test_inbox);
check('followed_at 有值', ($r->followed_at ?? 0) > 0);
echo "\n";

// 測試 2：重複追蹤（upsert，不應噴錯）
Follower::follow($test_account, $test_follower, $test_inbox, true);
$count = count(Follower::search(['account' => $test_account, 'follower' => $test_follower]));
check('重複追蹤不會建立重複記錄', $count === 1);
echo "\n";

// 測試 3：取消追蹤
Follower::follow($test_account, $test_follower, null, false);
$r = Follower::search(['account' => $test_account, 'follower' => $test_follower])->first();
check('取消追蹤後記錄仍存在', $r !== null);
check('status = 0', ($r->status ?? -1) == 0);
echo "\n";

// 清理
$r->delete();
echo "(已清除測試資料)\n";
echo "\n完成。\n";
