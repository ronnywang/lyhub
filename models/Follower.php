<?php

class Follower extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial', 'primary' => true];
        $this->_columns['account'] = ['type' => 'varchar', 'length' => 32];
        $this->_columns['follower'] = ['type' => 'varchar', 'length' => 128];
        // 1: following, 0: not following
        $this->_columns['status'] = ['type' => 'int'];
        $this->_columns['followed_at'] = ['type' => 'bigint'];
        $this->_columns['pushed_at'] = ['type' => 'bigint'];
    }

    public static function follow($account, $follower, $follow_or_unfollow = true)
    {
    }
}
