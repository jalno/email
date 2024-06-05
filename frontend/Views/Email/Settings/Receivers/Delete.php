<?php
namespace themes\clipone\Views\Email\Settings\Receivers;
use \packages\base\Translator;
use \packages\email\Views\Settings\Receivers\Delete as DeleteView;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
class Delete extends DeleteView{
	use ViewTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.receivers.delete"));
		Navigation::active("settings/email/receivers");
	}
}
