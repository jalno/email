<?php

namespace packages\email\Views\Settings\Senders;

use packages\email\Sender;
use packages\userpanel\Views\Form;

class Edit extends Form
{
    public function setSenders($senders)
    {
        $this->setData($senders, 'senders');
    }

    protected function getSenders()
    {
        return $this->getData('senders');
    }

    public function setSender(Sender $sender)
    {
        $this->setData($sender, 'sender');
        $this->setDataForm($sender->toArray());
        foreach ($sender->params as $param) {
            $this->setDataForm($param->value, $param->name);
        }
        foreach ($this->getSenders() as $s) {
            if ($s->getHandler() == $sender->handler) {
                $this->setDataForm($s->getName(), 'sender');
                break;
            }
        }
    }

    protected function getSender()
    {
        return $this->getData('sender');
    }
}
