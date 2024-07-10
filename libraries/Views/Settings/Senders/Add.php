<?php

namespace packages\email\Views\Settings\Senders;

use packages\email\Events\Senders;
use packages\userpanel\Views\Form;

class Add extends Form
{
    public function setsenders(Senders $senders)
    {
        $this->setData($senders, 'senders');
    }

    protected function getsenders()
    {
        return $this->getData('senders');
    }
}
