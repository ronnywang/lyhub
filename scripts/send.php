<?php

include(__DIR__ . "/../init.inc.php");

$account = 'mp-1160';
$domain = $_SERVER['HTTP_HOST'];
$mp_id = explode('-', $account)[1];
$mp_data = LYDataHelper::getMPData($mp_id);
$records = LYDataHelper::getMPRecords($mp_data);

/*$create_activity = [
    '@context' => 'https://www.w3.org/ns/activitystreams',
    'id' => "https://{$domain}/users/{$account}/followers/{$mp_id}",
    'type' => 'Follow',
    'actor' => "https://{$domain}/users/{$account}",
    'object' => "https://mastodon.ronny.tw/users/ronnywang",
];*/
for ($i = 0; $i < 10; $i ++) {
$create_activity = LYDataHelper::formatActivityObject($account, $records[$i]);
$create_activity['@context'] = [
    'https://www.w3.org/ns/activitystreams',
    [
        'ostatus' => 'http://ostatus.org#',
        'atomUri' => 'ostatus:inReplyToAtomUri',
        'conversation' => 'ostatus:conversation',
        'sensitive' => 'as:sensitive',
        'toot' => 'http://joinmastodon.org/ns#',
        'votersCount' => 'toot:votersCount',
        'blurhash' => 'toot:blurhash',
        'focalPoint' => [
            '@container' => '@list',
            '@id' => 'toot:focalPoint',
        ],
    ],
];
$inbox_url = "https://mastodon.ronny.tw/users/ronnywang/inbox";
ActivityPubHelper::send_signed_request($inbox_url, json_encode($create_activity), $account, $domain);
}
