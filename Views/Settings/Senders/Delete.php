<?php

namespace packages\email\Views\Settings\Senders;

use packages\email\Sender;
use packages\userpanel\Views\Form;

class Delete extends Form
{
    public function setSender(Sender $sender)
    {
        $this->setData($sender, 'sender');
    }

    protected function getSender()
    {
        return $this->getData('sender');
    }
}
