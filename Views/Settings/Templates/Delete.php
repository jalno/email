<?php

namespace packages\email\Views\Settings\Templates;

use packages\email\Template;
use packages\userpanel\Views\Form;

class Delete extends Form
{
    public function setTemplate(Template $template)
    {
        $this->setData($template, 'template');
    }

    protected function getTemplate()
    {
        return $this->getData('template');
    }
}
