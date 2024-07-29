<?php

namespace packages\email\Sender;

use packages\base\DB\DBObject;
use packages\email\Sender;

class Address extends DBObject
{
    public const active = 1;
    public const deactive = 2;
    protected $dbTable = 'email_senders_addresses';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'sender' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'address' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'sender' => ['hasOne', Sender::class, 'sender'],
    ];

    protected function byAddress($address)
    {
        $this->where('address', $address);

        return $this->getOne();
    }
}
