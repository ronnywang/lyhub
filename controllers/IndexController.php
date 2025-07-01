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
            $obj = LYAPI::apiQuery("/legislators?歷屆立法委員編號={$mp_id}&sort=屆", "取得立法委員資料");
            if (!$obj->legislators || !is_array($obj->legislators) || count($obj->legislators) === 0) {
                header('HTTP/1.1 404 Not Found');
                exit;
            }
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
                'href' => $obj->legislators[0]->照片位址,
            ];
        } elseif (preg_match('#^law-(\d+)$#', $username, $matches)) {
        } elseif (preg_match('#^committee-(\d+)$#', $username, $matches)) {
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        header('Content-Type: application/jrd+json; charset=utf-8');
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
        $response['following'] = "https://{$domain}/users/{$account}/following";
        $response['followers'] = "https://{$domain}/users/{$account}/followers";
        $response['inbox'] = "https://{$domain}/users/{$account}/inbox";
        $response['outbox'] = "https://{$domain}/users/{$account}/outbox";
        $response['featured'] = "https://{$domain}/users/{$account}/featured";
        $response['featuredTags'] = "https://{$domain}/users/{$account}/featured-tags";
        $response['preferredUsername'] = $account;
        $response['name'] = '';
        $response['summary'] = '';
        $response['url'] = "https://{$domain}/mp/{$mp_id}";
        $response['manuallyApprovesFollowers'] = false;
        $response['discoverable'] = true;
        $response['indexable'] = true;
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
            $obj = LYAPI::apiQuery("/legislators?歷屆立法委員編號={$mp_id}&sort=屆", "取得立法委員資料");
            if (!$obj->legislators || !is_array($obj->legislators) || count($obj->legislators) === 0) {
                header('HTTP/1.1 404 Not Found');
                exit;
            }
            $response['name'] = $obj->legislators[0]->委員姓名;
            $response['summary'] = sprintf("[國會資訊推播器]\n第%02d屆立法委員\n黨籍:%s，選區：%s\n委員會：%s",
                $obj->legislators[0]->屆,
                $obj->legislators[0]->黨籍,
                $obj->legislators[0]->選區名稱,
                $obj->legislators[0]->委員會[count($obj->legislators[0]->委員會) - 1],
            );
            $response['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => $obj->legislators[0]->照片位址,
            ];
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }
        return $this->json($response);
    }
}
