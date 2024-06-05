<?php

namespace packages\email;

use packages\base\Date;
use packages\base\DB\DBObject;
use packages\userpanel\User;

class Get extends DBObject
{
    public const unread = 1;
    public const read = 2;
    protected $dbTable = 'email_get';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'serverid' => ['type' => 'text'],
        'receive_at' => ['type' => 'int', 'required' => true],
        'sender_name' => ['type' => 'text'],
        'sender_address' => ['type' => 'text', 'required' => true],
        'sender_user' => ['type' => 'int'],
        'receiver' => ['type' => 'int', 'required' => true],
        'receiver_name' => ['type' => 'text'],
        'receiver_address' => ['type' => 'text', 'required' => true],
        'subject' => ['type' => 'text'],
        'text' => ['type' => 'text'],
        'html' => ['type' => 'text'],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'sender_user' => ['hasOne', User::class, 'sender_user'],
        'receiver' => ['hasOne', Receiver::class, 'receiver'],
    ];

    public function preLoad($data)
    {
        if (!isset($data['receive_at'])) {
            $data['receive_at'] = Date::time();
        }
        if (!isset($data['sender_user'])) {
            $user = new User();
            if ($user = $user->where('email', $data['sender_address'])->getOne()) {
                $data['sender_user'] = $user->id;
            }
        }
        if (!isset($data['status'])) {
            $data['status'] = self::unread;
        }

        return $data;
    }

    public function getContent(): string
    {
        return $this->html ? $this->html : $this->text;
    }
}
