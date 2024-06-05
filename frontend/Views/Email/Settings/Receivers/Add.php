<?php

namespace themes\clipone\Views\Email\Settings\Receivers;

use packages\base\Translator;
use packages\email\Receiver;
use packages\email\Views\Settings\Receivers\Add as AddView;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Add extends AddView
{
    use ViewTrait;
    use FormTrait;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.receivers.add'));
        $this->setNavigation();
        $this->addAssets();
    }

    public function addAssets()
    {
    }

    private function setNavigation()
    {
        $add = new Navigation\MenuItem('receiver_add');
        $add->setTitle(Translator::trans('add'));
        $add->setIcon('fa fa-plus');
        $add->setURL(userpanel\url('settings/email/receivers/add'));
        // breadcrumb::addItem($add);
        Navigation::active('settings/email/receivers');
    }

    public function getStatusForSelect()
    {
        return [
            [
                'title' => Translator::trans('email.receiver.status.active'),
                'value' => Receiver::active,
            ],
            [
                'title' => Translator::trans('email.receiver.status.deactive'),
                'value' => Receiver::deactive,
            ],
        ];
    }

    public function getTypesForSelect()
    {
        return [
            [
                'title' => 'IMAP',
                'value' => Receiver::IMAP,
            ],
            [
                'title' => 'POP3',
                'value' => Receiver::POP3,
            ],
            [
                'title' => 'NNTP',
                'value' => Receiver::NNTP,
            ],
        ];
    }

    public function getEncryptionsForSelect()
    {
        return [
            [
                'title' => '',
                'value' => '',
            ],
            [
                'title' => 'SSL',
                'value' => Receiver::SSL,
            ],
            [
                'title' => 'TLS',
                'value' => Receiver::TLS,
            ],
        ];
    }
}
