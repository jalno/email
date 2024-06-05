<?php

namespace themes\clipone\Views\Email\Settings\Senders;

use packages\base\Options;
use packages\base\Translator;
use packages\email\Sender;
use packages\email\Views\Settings\Senders\Edit as EditView;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Edit extends EditView
{
    use ViewTrait;
    use FormTrait;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.senders.edit'));
        $this->setNavigation();
        $this->addBodyClass('email_senders');
    }

    private function setNavigation()
    {
        Navigation::active('settings/email/senders');
    }

    public function getSendersForSelect()
    {
        $options = [];
        foreach ($this->getSenders() as $sender) {
            $title = Translator::trans('email.sender.'.$sender->getName());
            $options[] = [
                'value' => $sender->getName(),
                'title' => $title ? $title : $sender->getName(),
            ];
        }

        return $options;
    }

    public function getsenderstatusForSelect()
    {
        $options = [
            [
                'title' => Translator::trans('email.sender.status.active'),
                'value' => Sender::active,
            ],
            [
                'title' => Translator::trans('email.sender.status.deactive'),
                'value' => Sender::deactive,
            ],
        ];

        return $options;
    }

    protected function getAddressesData()
    {
        $addresses = [];
        foreach ($this->getsender()->addresses as $address) {
            $addressData = $address->toArray();
            if (Options::get('packages.email.defaultAddress') == $address->id) {
                $addressData['primary'] = true;
            }
            $addresses[] = $addressData;
        }

        return $addresses;
    }
}
