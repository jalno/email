<?php

namespace packages\email;

use packages\base\DB\DBObject;
use packages\base\Events;
use packages\email\Events as EmailEvents;

class Receiver extends DBObject
{
    public const active = 1;
    public const deactive = 2;
    public const IMAP = 1;
    public const POP3 = 2;
    public const NNTP = 3;
    public const SSL = 1;
    public const TLS = 2;
    protected $dbTable = 'email_receivers';
    protected $primaryKey = 'id';
    protected $driver;
    protected $dbFields = [
        'title' => ['type' => 'text', 'required' => true],
        'type' => ['type' => 'int', 'required' => true],
        'hostname' => ['type' => 'text', 'required' => true],
        'port' => ['type' => 'int', 'required' => true],
        'username' => ['type' => 'text', 'required' => true],
        'password' => ['type' => 'text', 'required' => true],
        // 'authentication' => array('type' => 'int', 'required' => true),
        'encryption' => ['type' => 'int'],
        'status' => ['type' => 'int', 'required' => true],
    ];

    protected function preLoad($data)
    {
        if (!$data['encryption']) {
            $data['encryption'] = 0;
        }

        return $data;
    }

    public function connect(): Imap\MailBox
    {
        $type = '';
        switch ($this->type) {
            case self::IMAP:$type = 'imap';
                break;
            case self::POP3:$type = 'pop3';
                break;
            case self::NNTP:$type = 'NNTP';
                break;
        }

        $encryption = '';
        switch ($this->encryption) {
            case self::SSL:$encryption = 'ssl';
                break;
            case self::TLS:$encryption = 'tls';
                break;
        }

        $path = '{'.$this->hostname.':'.$this->port.'/'.$type;
        if ($encryption) {
            $path .= '/'.$encryption;
        }
        $path .= '}INBOX';

        return $this->driver = new Imap\Mailbox($path, $this->username, $this->password);
    }

    public function check()
    {
        if (!$this->driver) {
            $this->connect();
        }

        return $this->driver->searchMailbox('UNSEEN');
    }

    public function getEmails($messages)
    {
        foreach ($messages as $message) {
            $this->getEmail($message);
        }
    }

    public function getEmail($message)
    {
        if ($email = $this->driver->getMail($message)) {
            $toEmails = array_keys($email->to);
            $toNames = array_values($email->to);
            $get = new Get();
            $get->serverid = $message;
            $get->receive_at = $email->time;
            $get->sender_name = $email->fromName;
            $get->sender_address = $email->fromAddress;
            $get->receiver = $this->id;
            $get->receiver_name = $toNames[0];
            $get->receiver_address = $toEmails[0];
            $get->subject = $email->subject;
            $get->text = $email->textPlain;
            $get->html = $email->textHtml;
            $get->save();
            $this->driver->markMailAsRead($message);
            foreach ($email->getAttachments() as $attachment) {
            }
            Events::trigger(new EmailEvents\Receive($get));
        }
    }
}
