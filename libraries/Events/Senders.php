<?php

namespace packages\email\Events;

use packages\base\Event;
use packages\email\Events\Senders\Sender;

class Senders extends Event
{
    private $senders = [];

    public function addSender(Sender $sender)
    {
        $this->senders[$sender->getName()] = $sender;
    }

    public function getSenderNames()
    {
        return array_keys($this->senders);
    }

    public function getByName($name)
    {
        return isset($this->senders[$name]) ? $this->senders[$name] : null;
    }

    public function getByHandler($handler)
    {
        foreach ($this->senders as $sender) {
            if ($sender->getHandler() == $handler) {
                return $sender;
            }
        }

        return null;
    }

    public function get()
    {
        return $this->senders;
    }
}
