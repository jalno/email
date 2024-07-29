<?php

namespace packages\email;

use packages\base\DB\DBObject;
use packages\userpanel\User;

class Sent extends DBObject
{
    public const queued = 1;
    public const sending = 2;
    public const sent = 3;
    public const failed = 4;
    protected $tmparams = [];
    protected $dbTable = 'email_sent';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'send_at' => ['type' => 'int', 'required' => true],
        'sender_address' => ['type' => 'text', 'required' => true],
        'sender_user' => ['type' => 'int'],
        'receiver_name' => ['type' => 'text'],
        'receiver_address' => ['type' => 'text', 'required' => true],
        'receiver_user' => ['type' => 'int'],
        'subject' => ['type' => 'text', 'required' => true],
        'text' => ['type' => 'text', 'required' => true],
        'html' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'sender_address' => ['hasOne', Sender\Address::class, 'sender_address'],
        'sender_user' => ['hasOne', User::class, 'sender_user'],
        'receiver_user' => ['hasOne', User::class, 'receiver_user'],
        'attachments' => ['hasMany', Sent\Attachment::class, 'mail'],
    ];

    public function preLoad($data)
    {
        if (!isset($data['send_at'])) {
            $data['send_at'] = time();
        }

        return $data;
    }

    public function send()
    {
        $this->status = self::sending;
        $this->save();
        try {
            $status = $this->sender_address->sender->send($this);
            if (in_array($status, [self::sent, self::failed])) {
                $this->status = $status;
            } else {
                $this->status = self::failed;
            }
        } catch (\Exception $e) {
            $this->status = self::failed;
        }
        $this->save();

        return self::sent == $this->status;
    }
}
