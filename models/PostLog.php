<?php

class PostLog extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial'];
        $this->_columns['post_at'] = ['type' => 'bigint'];
        $this->_columns['post_from'] = ['type' => 'varchar', 'length' => 64];
        $this->_columns['type'] = ['type' => 'varchar', 'length' => 32];
        $this->_columns['data'] = ['type' => 'text'];
    }
}
