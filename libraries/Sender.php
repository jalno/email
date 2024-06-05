<?php

namespace packages\email;

use packages\base\DB\DBObject;
use packages\email\Sender\Param;

class Sender extends DBObject
{
    public const active = 1;
    public const deactive = 2;
    protected $dbTable = 'email_senders';
    protected $primaryKey = 'id';
    private $handlerClass;
    protected $dbFields = [
        'title' => ['type' => 'text', 'required' => true],
        'handler' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'addresses' => ['hasMany', Sender\Address::class, 'sender'],
        'params' => ['hasMany', Param::class, 'sender'],
    ];

    public function __construct($data = null, $connection = 'default')
    {
        $data = $this->processData($data);
        parent::__construct($data, $connection);
    }
    protected $tmparams = [];

    private function processData($data)
    {
        $newdata = [];
        if (is_array($data)) {
            if (isset($data['params'])) {
                foreach ($data['params'] as $name => $value) {
                    $this->tmparams[$name] = new Param([
                        'name' => $name,
                        'value' => $value,
                    ]);
                }
                unset($data['params']);
            }
            $newdata = $data;
        }

        return $newdata;
    }

    public function setParam($name, $value)
    {
        $param = false;
        foreach ($this->params as $p) {
            if ($p->name == $name) {
                $param = $p;
                break;
            }
        }
        if (!$param) {
            $param = new Param([
                'name' => $name,
                'value' => $value,
            ]);
        } else {
            $param->value = $value;
        }

        if (!$this->id) {
            $this->tmparams[$name] = $param;
        } else {
            $param->sender = $this->id;

            return $param->save();
        }
    }

    public function save($data = null)
    {
        if ($return = parent::save($data)) {
            foreach ($this->tmparams as $param) {
                $param->sender = $this->id;
                $param->save();
            }
            $this->tmparams = [];
        }

        return $return;
    }

    public function param($name)
    {
        if (!$this->id) {
            return isset($this->tmparams[$name]) ? $this->tmparams[$name]->value : null;
        } else {
            foreach ($this->params as $param) {
                if ($param->name == $name) {
                    return $param->value;
                }
            }

            return false;
        }
    }

    public function getController()
    {
        if ($this->handlerClass) {
            return $this->handlerClass;
        }
        if (class_exists($this->handler)) {
            $this->handlerClass = new $this->handler($this);

            return $this->handlerClass;
        }

        return false;
    }

    public function send(Sent $email)
    {
        return $this->getController()->send($email);
    }
}
