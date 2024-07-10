<?php

namespace packages\email\Views\Sent;

use packages\base\Views\Traits\Form as FormTrait;
use packages\email\Authorization;

class ListView extends \packages\email\Views\ListView
{
    use FormTrait;
    protected $canSend;
    protected static $navigation;

    public function __construct()
    {
        $this->canSend = Authorization::is_accessed('send');
    }

    public static function onSourceLoad()
    {
        self::$navigation = Authorization::is_accessed('sent_list');
    }
}
