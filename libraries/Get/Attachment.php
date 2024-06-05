<?php

namespace packages\email\Get;

use packages\base\DB\DBObject;

class Attachment extends DBObject
{
    protected $dbTable = 'email_get_attachments';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'mail' => ['type' => 'int', 'required' => true],
        'size' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'file' => ['type' => 'text', 'required' => true],
    ];
    protected $relations = [
        'mail' => ['hasOne', \packages\email\Get::class, 'mail'],
    ];
}
