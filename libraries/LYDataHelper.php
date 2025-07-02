<?php

class LYDataHelper
{
    public static function formatActivityObject($account, $record)
    {
        $ret = [];
        if ($record[0] == 'bill-first') {
            $id = $record[2]->議案編號 ?? '';
            $content = "【提案】{$record[2]->議案名稱} ({$record[2]->議案編號})\n提案日期：{$record[2]->提案日期}\n\n";
        } elseif ($record[0] == 'bill-second') {
            $id = $record[2]->議案編號 ?? '';
            $content = "【連署】{$record[2]->議案名稱} ({$record[2]->議案編號})\n提案日期：{$record[2]->提案日期}\n\n";
        } else {
            $id = $record[2]->IVOD_ID;
            $content = "【影音】{$record[2]->標題}\n開始時間：{$record[2]->開始時間}\n\n";
        }
        $ret['id'] = "https://{$_SERVER['HTTP_HOST']}/users/{$account}/statuses/{$record[0]}-{$id}";
        $ret['type'] = 'Create';
        $ret['published'] = date('Y-m-d\TH:i:sP', $record[1]);
        $ret['to'] = ['https://www.w3.org/ns/activitystreams#Public'];
        $ret['actor'] = "https://{$_SERVER['HTTP_HOST']}/users/{$account}";
        $ret['object'] = [
            'id' => $ret['id'],
            'type' => 'Note',
            'summary' => null,
            'url' => "https://{$_SERVER['HTTP_HOST']}/users/{$account}/statuses/{$record[0]}-{$id}",
            'published' => $ret['published'],
            'attributedTo' => "https://{$_SERVER['HTTP_HOST']}/users/{$account}",
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'content' => $content,
        ];
        return $ret;
    }


    public static function getMPData($mp_id)
    {
        $obj = LYAPI::apiQuery("/legislators?歷屆立法委員編號={$mp_id}&sort=屆", "取得立法委員資料");
        if (!$obj->legislators || !is_array($obj->legislators) || count($obj->legislators) === 0) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
        return $obj->legislators[0];
    }
}
