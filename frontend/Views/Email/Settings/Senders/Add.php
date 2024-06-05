<?php

namespace themes\clipone\Views\Email\Settings\Senders;

use packages\base\Translator;
use packages\email\Sender;
use packages\email\Views\Settings\Senders\Add as AddView;
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
        $this->setTitle(Translator::trans('settings.email.senders.add'));
        $this->setNavigation();
        $this->addBodyClass('email_senders');
    }

    private function setNavigation()
    {
        $add = new Navigation\MenuItem('sender_add');
        $add->setTitle(Translator::trans('add'));
        $add->setIcon('fa fa-plus');
        $add->setURL(userpanel\url('settings/email/senders/add'));
        // breadcrumb::addItem($add);
        Navigation::active('settings/email/senders');
    }

    public function getsendersForSelect()
    {
        $options = [];
        foreach ($this->getsenders()->get() as $sender) {
            $title = Translator::trans('email.sender.'.$sender->getName());
            $options[] = [
                'value' => $sender->getName(),
                'title' => $title ? $title : $sender->getName(),
            ];
        }

        return $options;
    }

    public function getsenderStatusForSelect()
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
}
