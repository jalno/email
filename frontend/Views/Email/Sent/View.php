<?php

namespace themes\clipone\Views\Email\Sent;

use packages\email\Views\Sent\View as SentView;
use themes\clipone\ViewTrait;

class View extends SentView
{
    use ViewTrait;
    protected $email;

    public function __beforeLoad()
    {
        $this->email = $this->getEmail();
    }
}
