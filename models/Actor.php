<?php

class Actor extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial', 'primary' => true];
        $this->_columns['actor_id'] = ['type' => 'varchar', 'length' => 255, 'notnull' => true];
        $this->_columns['created_at'] = ['type' => 'bigint'];
        $this->_columns['updated_at'] = ['type' => 'bigint'];
        $this->_columns['data'] = ['type' => 'jsonb'];

        $this->_indexes['actor_actor_id'] = ['columns' => ['actor_id'], 'unique' => true];
    }
}

