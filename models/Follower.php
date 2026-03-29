<?php

class Follower extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial', 'primary' => true];
        $this->_columns['account'] = ['type' => 'varchar', 'length' => 32];
        $this->_columns['follower'] = ['type' => 'varchar', 'length' => 512];
        $this->_columns['inbox'] = ['type' => 'varchar', 'length' => 512];
        // 1: following, 0: not following
        $this->_columns['status'] = ['type' => 'int'];
        $this->_columns['followed_at'] = ['type' => 'bigint'];
        $this->_columns['pushed_at'] = ['type' => 'bigint'];

        $this->_indexes['follower_account_follower'] = ['columns' => ['account', 'follower'], 'unique' => true];
    }

    public static function follow($account, $follower_url, $inbox_url = null, $follow_or_unfollow = true)
    {
        try {
            Follower::insert([
                'account' => $account,
                'follower' => $follower_url,
                'inbox' => $inbox_url,
                'status' => $follow_or_unfollow ? 1 : 0,
                'followed_at' => time(),
                'pushed_at' => 0,
            ]);
        } catch (MiniEngine_Table_DuplicateException $e) {
            $record = Follower::search(['account' => $account, 'follower' => $follower_url])->first();
            $update = ['status' => $follow_or_unfollow ? 1 : 0];
            if ($follow_or_unfollow) {
                $update['followed_at'] = time();
                if ($inbox_url) {
                    $update['inbox'] = $inbox_url;
                }
            }
            $record->update($update);
        }
    }
}
