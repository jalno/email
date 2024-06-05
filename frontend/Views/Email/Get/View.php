<?php

namespace themes\clipone\Views\Email\Get;

use packages\email\Views\Get\View as GetView;
use themes\clipone\ViewTrait;

class View extends GetView
{
    use ViewTrait;
    protected $email;

    public function __beforeLoad()
    {
        $this->email = $this->getEmail();
    }
}
