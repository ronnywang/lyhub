<?php

class IndexController extends MiniEngine_Controller
{
    public function indexAction()
    {
        $this->view->app_name = getenv('APP_NAME');
    }

    public function robotsAction()
    {
        header('Content-Type: text/plain');
        echo "#\n";
        return $this->noview();
    }

    public function webfingerAction()
    {
        $resource = $_GET['resource'] ?? '';
        if (strpos($resource, 'acct:') !== 0) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }
        $account = explode(':', $resource)[1];
        list($username, $domain) = explode('@', $account);
        $response = [
            'subject' => "acct:{$username}@{$domain}",
            'links' => [[
                'rel' => 'self',
                'type' => 'application/activity+json',
                'href' => "https://{$domain}/users/{$username}",
            ]],
        ];
        if (preg_match('#^mp-(\d+)$#', $username, $matches)) {
            $mp_id = $matches[1];
            $mp_data = LYDataHelper::getMPData($mp_id);
            $response['aliases'] = [
                "https://{$_SERVER['HTTP_HOST']}/mp/{$mp_id}",
            ];
            $response['links'][] = [
                'rel' => 'http://webfinger.net/rel/profile-page',
                'type' => 'text/html',
                'href' => "https://{$_SERVER['HTTP_HOST']}/mp/{$mp_id}",
            ];
            $response['links'][] = [
                'rel' => 'http://webfinger.net/rel/avatar',
                'type' => 'image/png',
                'href' => $mp_data->照片位址,
            ];
        } elseif (preg_match('#^law-(\d+)$#', $username, $matches)) {
        } elseif (preg_match('#^committee-(\d+)$#', $username, $matches)) {
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $this->header('Content-Type: application/jrd+json; charset=utf-8');
        return $this->json($response);
    }

    public function userAction()
    {
        $account = urldecode(explode('/', $_SERVER['REQUEST_URI'])[2] ?? '');
        $domain = $_SERVER['HTTP_HOST'];

        $this->header('Content-Type: application/activity+json; charset=utf-8');
        $response = [];
        $response['@context'] = [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
            [
                'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
                'toot' => 'http://joinmastodon.org/ns#',
                'featured' => [
                    '@id' => 'toot:featured',
                    '@type' => '@id',
                ],
                'featuredTags' => [
                    '@id' => 'toot:featuredTags',
                    '@type' => '@id',
                ],
                'alsoKnownAs' => [
                    '@id' => 'as:alsoKnownAs',
                    '@type' => '@id',
                ],
                'movedTo' => [
                    '@id' => 'as:movedTo',
                    '@type' => '@id',
                ],
                'schema' => 'http://schema.org#',
                'PropertyValue' => 'schema:PropertyValue',
                'value' => 'schema:value',
                'discoverable' => 'toot:discoverable',
                'suspended' => 'toot:suspended',
                'memorial' => 'toot:memorial',
                'indexable' => 'toot:indexable',
                'attributionDomains' => [
                    '@id' => 'toot:attributionDomains',
                    '@type' => '@id',
                ],
                'Emoji' => 'toot:Emoji',
                'focalPoint' => [
                    '@container' => '@list',
                    '@id' => 'toot:focalPoint',
                ],
            ],
        ];
        $response['id'] = "https://{$domain}/users/{$account}";
        $response['type'] = 'Person';
        //$response['following'] = "https://{$domain}/users/{$account}/following";
        //$response['followers'] = "https://{$domain}/users/{$account}/followers";
        $response['inbox'] = "https://{$domain}/users/{$account}/inbox";
        $response['outbox'] = "https://{$domain}/users/{$account}/outbox";
        //$response['featured'] = "https://{$domain}/users/{$account}/featured";
        //$response['featuredTags'] = "https://{$domain}/users/{$account}/featured-tags";
        $response['preferredUsername'] = $account;
        $response['name'] = '';
        $response['summary'] = '';
        $response['url'] = '';
        $response['manuallyApprovesFollowers'] = false;
        $response['discoverable'] = false;
        $response['indexable'] = false;
        $response['published'] = date('Y-m-d\TH:i:sP');
        $response['memorial'] = false;
        $response['publicKey'] = [
            'id' => "https://{$domain}/users/{$account}#main-key",
            'owner' => "https://{$domain}/users/{$account}",
            'publicKeyPem' => (file_get_contents(__DIR__ . "/../config/public")),
        ];
        $response['tag'] = [];
        $response['attachment'] = [];
        $response['endpoints'] = [
            'sharedInbox' => "https://{$domain}/inbox",
        ];
        if (preg_match('#^mp-(\d+)$#', $account, $matches)) {
            $mp_id = $matches[1];
            $mp_data = LYDataHelper::getMPData($mp_id);
            $response['name'] = $mp_data->委員姓名;
            $response['summary'] = sprintf("[國會資訊推播器]\n第%02d屆立法委員\n黨籍:%s，選區：%s\n委員會：%s",
                $mp_data->屆,
                $mp_data->黨籍,
                $mp_data->選區名稱,
                $mp_data->委員會[count($mp_data->委員會) - 1],
            );
            $response['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => $mp_data->照片位址,
            ];
            $response['url'] = "https://{$domain}/mp/{$mp_id}";
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }
        return $this->json($response);
    }

    public function outbox_cursor($account, $domain, $cursor)
    {
        $response = [];
        $response['@context'] = [
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
        $response['id'] = "https://{$domain}/users/{$account}/outbox?cursor={$cursor}";
        $response['type'] = 'OrderedCollectionPage';
        $response['partOf'] = "https://{$domain}/users/{$account}/outbox";
        $response['next'] = "https://{$domain}/users/{$account}/outbox?cursor=" . ($cursor + 1);
        $response['orderedItems'] = [];
        $records = [];
        if (preg_match('#^mp-(\d+)$#', $account, $matches)) {
            $mp_id = $matches[1];
            $mp_data = LYDataHelper::getMPData($mp_id);
            $bill_fields = '&output_fields=議案名稱&output_fields=提案日期&output_fields=議案編號';
            $obj = LYAPI::apiQuery("/bills?連署人={$mp_data->委員姓名}&limit=20{$bill_fields}", "取得連署列表");
            foreach ($obj->bills as $bill) {
                $records[] = ['bill-second', strtotime($bill->提案日期), $bill];
            }
            $obj = LYAPI::apiQuery("/bills?提案人={$mp_data->委員姓名}&limit=20{$bill_fields}", "取得提案列表");
            foreach ($obj->bills as $bill) {
                $records[] = ['bill-first', strtotime($bill->提案日期), $bill];
            }
            $obj = LYAPI::apiQuery("/ivods?委員名稱={$mp_data->委員姓名}&limit=20", "取得影音列表");
            foreach ($obj->ivods as $ivod) {
                $records[] = ['ivod', strtotime($ivod->開始時間), $ivod];
            }
            usort($records, function ($a, $b) {
                if ($b[1] != $a[1]) {
                    return $b[1] <=> $a[1];
                }
                // 如果相同，就依照 bill提案 > bill連署 > ivod 的順序
                $order = ['bill-first' => 1, 'bill-second' => 2, 'ivod' => 3];
                return $order[$a[0]] <=> $order[$b[0]];
            });
            $records = array_slice($records, 0, 20);
            foreach ($records as $record) {
                $response['orderedItems'][] = LYDataHelper::formatActivityObject($account, $record);
            }
        }
        $this->header('Content-Type: application/activity+json; charset=utf-8');
        return $this->json($response);
    }

	public function inboxAction()
    {
        $domain = $_SERVER['HTTP_HOST'];
        $account = urldecode(explode('/', $_SERVER['REQUEST_URI'])[2] ?? '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body);

        if (!ActivityPubHelper::verify_http_signature($_SERVER, $request_body)) {
            header('HTTP/1.1 401 Unauthorized');
            echo 'Signature verification failed.';
            exit;
        }

        if (($data->type ?? '') == 'Follow') {
            $follower_actor_url = $data->actor ?? '';
            $follower_actor_data = json_decode(file_get_contents($follower_actor_url, false, stream_context_create(['http' => ['header' => 'Accept: application/activity+json']])));
            $follower_inbox_url = $follower_actor_data->inbox ?? '';

            $follower_jsonl_file = __DIR__ . "/../data/followers-{$account}.jsonl";
            file_put_contents($follower_jsonl_file, json_encode([
                'type' => 'Follow',
                'actor' => $follower_actor_url,
                'object' => "https://{$domain}/users/{$account}",
                'data' => $data,
            ]) . "\n", FILE_APPEND);

            ActivityPubHelper::send_accept_activity($data, $account, $follower_inbox_url, $domain);
            header('HTTP/1.1 202 Accepted');
            exit;
		} else if (($data->type ?? '') == 'Undo') {
            $follower_actor_url = $data->actor ?? '';
            file_put_contents(__DIR__ . "/../data/followers-{$account}.jsonl", json_encode([
                'type' => 'Undo',
                'actor' => $follower_actor_url,
                'object' => "https://{$domain}/users/{$account}",
                'data' => $data,
            ]) . "\n", FILE_APPEND);
            header('HTTP/1.1 202 Accepted');
            exit;
        }
	}

    public function outboxAction()
    {
        $account = urldecode(explode('/', $_SERVER['REQUEST_URI'])[2] ?? '');
        $domain = $_SERVER['HTTP_HOST'];

        if (array_key_exists('cursor', $_GET)) {
            return $this->outbox_cursor($account, $domain, $_GET['cursor']);
        }
        $response = [];
        $response['@context'] = 'https://www.w3.org/ns/activitystreams';
        $response['id'] = "https://{$domain}/users/{$account}/outbox";
        $response['type'] = 'OrderedCollection';
        $response['totalItems'] = 0;
        if (preg_match('#^mp-(\d+)$#', $account, $matches)) {
            $mp_id = $matches[1];
            $mp_data = LYDataHelper::getMPData($mp_id);
            $obj = LYAPI::apiQuery("/bills?連署人={$mp_data->委員姓名}&limit=0", "取得連署數量");
            $response['totalItems'] = $obj->total;
            $obj = LYAPI::apiQuery("/bills?提案人={$mp_data->委員姓名}&limit=0", "取得提案數量");
            $response['totalItems'] += $obj->total;
            $obj = LYAPI::apiQuery("/ivods?委員名稱={$mp_data->委員姓名}&limit=0", "取得影音數量");
            $response['totalItems'] += $obj->total;
        }
        $response['first'] = 'https://' . $domain . '/users/' . $account . '/outbox?cursor=0';
        $response['last'] = 'https://' . $domain . '/users/' . $account . '/outbox?cursor=-1';
        $this->header('Content-Type: application/activity+json; charset=utf-8');
        return $this->json($response);
    }
}
