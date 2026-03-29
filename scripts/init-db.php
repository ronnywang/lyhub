<?php
include(__DIR__ . '/../init.inc.php');

$tables = [
    'Actor' => fn() => Actor::createTable(),
    'Follower' => fn() => Follower::createTable(),
    'PostLog' => fn() => PostLog::createTable(),
    'PushedActivity' => fn() => PushedActivity::createTable(),
];

foreach ($tables as $name => $create) {
    try {
        $create();
        echo "✓ {$name} 建立成功\n";
    } catch (Exception $e) {
        echo "✗ {$name} 建立失敗：{$e->getMessage()}\n";
    }
}
