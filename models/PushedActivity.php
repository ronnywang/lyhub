<?php

class PushedActivity extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial', 'primary' => true];
        $this->_columns['account'] = ['type' => 'varchar', 'length' => 32];
        // e.g. bill-first-1234567, bill-second-1234567, ivod-9999
        $this->_columns['activity_key'] = ['type' => 'varchar', 'length' => 128];
        $this->_columns['pushed_at'] = ['type' => 'bigint'];

        $this->_indexes['pushed_activity_account_key'] = ['columns' => ['account', 'activity_key'], 'unique' => true];
    }

    public static function isPushed($account, $activity_key)
    {
        return (bool) PushedActivity::search(['account' => $account, 'activity_key' => $activity_key])->first();
    }

    public static function markPushed($account, $activity_key)
    {
        try {
            PushedActivity::insert([
                'account' => $account,
                'activity_key' => $activity_key,
                'pushed_at' => time(),
            ]);
        } catch (MiniEngine_Table_DuplicateException $e) {
            // 已推播過，不需要更新
        }
    }
}
