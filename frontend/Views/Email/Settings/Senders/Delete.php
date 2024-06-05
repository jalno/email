<?php

namespace themes\clipone\Views\Email\Settings\Senders;

use packages\base\Translator;
use packages\email\Views\Settings\Senders\Delete as DeleteView;
use themes\clipone\Navigation;
use themes\clipone\ViewTrait;

class Delete extends DeleteView
{
    use ViewTrait;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.senders.delete'));
        Navigation::active('settings/email/senders');
    }
}
