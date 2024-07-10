<?php

namespace packages\email\Views\Settings\Senders;

use packages\base\Views\Traits\Form as FormTrait;
use packages\email\Authorization;
use packages\email\Events\Senders;

class ListView extends \packages\userpanel\Views\ListView
{
    use FormTrait;
    protected $canAdd;
    protected $canEdit;
    protected $canDel;
    protected static $navigation;

    public function __construct()
    {
        $this->canAdd = Authorization::is_accessed('settings_senders_add');
        $this->canEdit = Authorization::is_accessed('settings_senders_edit');
        $this->canDel = Authorization::is_accessed('settings_senders_delete');
    }

    public function getSenders()
    {
        return $this->getData('senders');
    }

    public function setSenders(Senders $senders)
    {
        $this->setData($senders, 'senders');
    }

    public static function onSourceLoad()
    {
        self::$navigation = Authorization::is_accessed('settings_senders_list');
    }
}
