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

    public static function getMPRecords($mp_data)
    {
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
        return $records;
    }
}
