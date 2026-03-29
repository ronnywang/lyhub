<?php

class ViewerController extends MiniEngine_Controller
{
    public function indexAction()
    {
        $obj = LYAPI::apiQuery("/legislators?屆=11&limit=200", "取得第11屆立法委員列表");
        $legislators = $obj->legislators ?? [];

        // 依黨籍排序
        usort($legislators, function ($a, $b) {
            $party_order = ['中國國民黨' => 1, '民主進步黨' => 2, '台灣民眾黨' => 3];
            $ao = $party_order[$a->黨籍] ?? 99;
            $bo = $party_order[$b->黨籍] ?? 99;
            if ($ao != $bo) return $ao <=> $bo;
            return strcmp($a->委員姓名, $b->委員姓名);
        });

        // 一次查出所有帳號的追蹤人數
        $follower_counts = [];
        foreach (Follower::search(['status' => 1])->toArray() as $row) {
            $follower_counts[$row['account']] = ($follower_counts[$row['account']] ?? 0) + 1;
        }

        $this->view->legislators = $legislators;
        $this->view->domain = $_SERVER['HTTP_HOST'];
        $this->view->follower_counts = $follower_counts;
    }

    public function accountAction()
    {
        $account = urldecode(explode('/', $_SERVER['REQUEST_URI'])[2] ?? '');
        $domain = $_SERVER['HTTP_HOST'];

        if (!preg_match('#^mp-(\d+)$#', $account, $matches)) {
            header('HTTP/1.1 404 Not Found');
            echo '找不到此帳號';
            exit;
        }

        $mp_id = $matches[1];
        $mp_data = LYDataHelper::getMPData($mp_id);
        $records = LYDataHelper::getMPRecords($mp_data);
        $follower_count = count(Follower::search(['account' => $account, 'status' => 1]));

        $this->view->account = $account;
        $this->view->domain = $domain;
        $this->view->mp_data = $mp_data;
        $this->view->records = $records;
        $this->view->follower_count = $follower_count;
        $this->view->actor_url = "https://{$domain}/users/{$account}";
        $this->view->outbox_url = "https://{$domain}/users/{$account}/outbox";
        $this->view->followers_url = "https://{$domain}/users/{$account}/followers";
    }
}
