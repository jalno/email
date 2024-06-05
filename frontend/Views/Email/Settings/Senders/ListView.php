<?php

namespace themes\clipone\Views\Email\Settings\Senders;

use packages\base\Translator;
use packages\email\Views\Settings\Senders\ListView as SendersListview;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class ListView extends SendersListview
{
    use ViewTrait;
    use ListTrait;
    use FormTrait;
    private $categories;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.senders'));
        Navigation::active('settings/email/senders');
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
            $senders = new MenuItem('senders');
            $senders->setTitle(Translator::trans('settings.email.senders'));
            $senders->setURL(userpanel\url('settings/email/senders'));
            $senders->setIcon('fa fa-rss');
            $email->addItem($senders);
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
}
