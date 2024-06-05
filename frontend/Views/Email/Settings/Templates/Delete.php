<?php

namespace themes\clipone\Views\Email\Settings\Templates;

use packages\base\Translator;
use packages\email\Views\Settings\Templates\Delete as DeleteView;
use themes\clipone\Navigation;
use themes\clipone\ViewTrait;

class Delete extends DeleteView
{
    use ViewTrait;

    public function __beforeLoad()
    {
        $this->setTitle(Translator::trans('settings.email.templates.delete'));
        Navigation::active('settings/email/templates');
    }
}
