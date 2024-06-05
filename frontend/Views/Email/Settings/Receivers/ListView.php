<?php

namespace themes\clipone\Views\Email\Settings\Receivers;

use packages\base\Translator;
use packages\email\Receiver;
use packages\email\Views\Settings\Receivers\ListView as ReceiversListview;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class ListView extends ReceiversListview
{
    use ViewTrait;
    use ListTrait;
    use FormTrait;
    private $categories;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.receivers'));
        Navigation::active('settings/email/receivers');
        $this->setButtons();
        $this->addAssets();
    }

    private function addAssets()
    {
    }

    public function getComparisonsForSelect()
    {
        return [
            [
                'title' => Translator::trans('search.comparison.contains'),
                'value' => 'contains',
            ],
            [
                'title' => Translator::trans('search.comparison.equals'),
                'value' => 'equals',
            ],
            [
                'title' => Translator::trans('search.comparison.startswith'),
                'value' => 'startswith',
            ],
        ];
    }

    public static function onSourceLoad()
    {
        parent::onSourceLoad();
        if (parent::$navigation) {
            $settings = Navigation::getByName('settings');
            if (!$email = Navigation::getByName('settings/email')) {
                $email = new MenuItem('email');
                $email->setTitle(Translator::trans('settings.email'));
                $email->setIcon('fa fa-envelope');
                if ($settings) {
                    $settings->addItem($email);
                }
            }
            $receivers = new MenuItem('receivers');
            $receivers->setTitle(Translator::trans('settings.email.receivers'));
            $receivers->setURL(userpanel\url('settings/email/receivers'));
            $receivers->setIcon('fa fa-angle-double-down');
            $email->addItem($receivers);
        }
    }

    public function setButtons()
    {
        $this->setButton('edit', $this->canEdit, [
            'title' => Translator::trans('edit'),
            'icon' => 'fa fa-edit',
            'classes' => ['btn', 'btn-xs', 'btn-teal'],
        ]);
        $this->setButton('delete', $this->canDel, [
            'title' => Translator::trans('delete'),
            'icon' => 'fa fa-times',
            'classes' => ['btn', 'btn-xs', 'btn-bricky'],
        ]);
    }

    public function getTypesForSelect()
    {
        return [
            [
                'title' => '',
                'value' => '',
            ],
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

    public function getStatusForSelect()
    {
        return [
            [
                'title' => '',
                'value' => '',
            ],
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
}
