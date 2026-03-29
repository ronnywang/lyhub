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

    public static function findByURL($url)
    {
        if (!$actor = Actor::search(['actor_id' => $url])->first() or $actor->updated_at < time() - 86400) {
            $data = json_decode(file_get_contents($follower_actor_url, false, stream_context_create(['http' => ['header' => 'Accept: application/activity+json']])));
            if (($data->id ?? false) != $url or ($data->type ?? false) != 'Person') {
                throw new Exception('Invalid actor data');
            }
            try {
                Actor::insert([
                    'actor_id' => $data->id,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'data' => $data,
                ]);
            } catch (MiniEngine_Table_DuplicateException $e) {
                Actor::search(['actor_id' => $url])->first()->update([
                    'updated_at' => time(),
                    'data' => $data,
                ]);
            }
            return $data;
        }
        return $actor->data;
    }
}

