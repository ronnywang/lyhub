<?php

class LYDataHelper
{
    public static function formatActivityObject($account, $record)
    {
        $ret = [];
        if ($record[0] == 'bill-first' or $record[0] == 'bill-second') {
            $id = $record[2]->議案編號 ?? '';
            $content = sprintf("<p>{$record[2]->議案名稱}</p>\n")
                .sprintf("<p>提案人：%s</p>\n", implode(',', $record[2]->提案人))
                .sprintf("<p>連署人：%s</p>\n", implode(',', $record[2]->連署人))
                .sprintf('<p>案由：%s</p>', mb_strimwidth($record[2]->案由, 0, 200, '...'))
                .sprintf('<p><a href="%s" target="_blank" rel="nofollow noopener noreferrer" translate="no"><span class="invisible">https://</span><span class="">%s</span><span class="invisible"></span></a></p>',
                    "https://ppg.ly.gov.tw/ppg/bills/{$record[2]->議案編號}/details",
                    '立法院議事暨公報資訊網議案資料')
                ;
        } else {
            $id = $record[2]->IVOD_ID;
            $content = sprintf("<p>IVOD 會議發言：%s %s</p>", $record[2]->日期, $record[2]->委員發言時間)
                . sprintf("<p>%s</p>", mb_strimwidth($record[2]->會議名稱, 0, 200, '...'))
                . sprintf('<p><a href="%s" target="_blank" rel="nofollow noopener noreferrer" translate="no"><span class="invisible">https://</span><span class="">%s</span><span class="invisible"></span></a></p>',
                    "https://ivod.ly.gov.tw/Play/Clip/1M/{$id}",
                    '立法院 議事轉播 網際網路多媒體隨選視訊(ivod)系統')
                ;
        }
        $ret['id'] = "https://{$_SERVER['HTTP_HOST']}/users/{$account}/statuses/{$record[0]}-{$id}";
        $ret['type'] = 'Create';
        $ret['actor'] = "https://{$_SERVER['HTTP_HOST']}/users/{$account}";
        $ret['published'] = date('Y-m-d\TH:i:sP', $record[1]);
        $ret['to'] = ['https://www.w3.org/ns/activitystreams#Public'];
        $ret['cc'] = ["https://{$_SERVER['HTTP_HOST']}/users/{$account}/followers"];
        $ret['object'] = [
            'id' => $ret['id'],
            'type' => 'Note',
            'summary' => null,
            'url' => "https://{$_SERVER['HTTP_HOST']}/users/{$account}/statuses/{$record[0]}-{$id}",
            'published' => $ret['published'],
            'attributedTo' => "https://{$_SERVER['HTTP_HOST']}/users/{$account}",
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'cc' => ["https://{$_SERVER['HTTP_HOST']}/users/{$account}/followers"],
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
        $bill_fields = '&output_fields=議案名稱&output_fields=提案日期&output_fields=議案編號&output_fields=提案人&output_fields=連署人&output_fields=案由';
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
