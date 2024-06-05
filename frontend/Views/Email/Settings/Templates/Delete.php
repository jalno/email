<?php
namespace themes\clipone\Views\Email\Settings\Templates;
use \packages\base\Translator;
use \packages\userpanel;
use \packages\email\Views\Settings\Templates\Delete as DeleteView;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;

class Delete extends DeleteView{
	use viewTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.templates.delete"));
		Navigation::active("settings/email/templates");
	}
}
