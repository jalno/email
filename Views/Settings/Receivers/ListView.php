<?php

namespace packages\email\Views\Settings\Receivers;

use packages\base\Views\Traits\Form as FormTrait;
use packages\email\Authorization;

class ListView extends \packages\userpanel\Views\ListView
{
    use FormTrait;
    protected $canAdd;
    protected $canEdit;
    protected $canDel;
    protected static $navigation;

    public function __construct()
    {
        $this->canAdd = Authorization::is_accessed('settings_receivers_add');
        $this->canEdit = Authorization::is_accessed('settings_receivers_edit');
        $this->canDel = Authorization::is_accessed('settings_receivers_delete');
    }

    public static function onSourceLoad()
    {
        self::$navigation = Authorization::is_accessed('settings_receivers_list');
    }
}
