<?php

namespace packages\email\Views;

class Send extends Form
{
    public function setAddresses($addresses)
    {
        $this->setData($addresses, 'addresses');
    }

    protected function getAddresses()
    {
        return $this->getData('addresses');
    }
}
