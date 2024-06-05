<?php

namespace packages\email\Sender;

use packages\base\DB\DBObject;

class Param extends DBObject
{
    protected $dbTable = 'email_senders_params';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'sender' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'value' => ['type' => 'text'],
    ];
}
