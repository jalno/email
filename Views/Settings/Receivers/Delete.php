<?php

namespace packages\email\Views\Settings\Receivers;

use packages\email\Receiver;
use packages\userpanel\Views\Form;

class Delete extends Form
{
    public function setReceiver(Receiver $receiver)
    {
        $this->setData($receiver, 'receiver');
    }

    protected function getReceiver()
    {
        return $this->getData('receiver');
    }
}
