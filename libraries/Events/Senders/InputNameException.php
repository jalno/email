<?php

namespace packages\email\Events\Senders;

class InputNameException extends \Exception
{
    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getController()
    {
        return $this->input;
    }
}
