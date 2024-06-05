<?php

namespace packages\email\Events;

use packages\base\Event;
use packages\email\Get;

class Receive extends Event
{
    protected $email;

    public function __construct(Get $email)
    {
        $this->email = $email;
    }

    public function getemail()
    {
        return $this->email;
    }
}
