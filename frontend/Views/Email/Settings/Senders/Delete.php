<?php
namespace themes\clipone\Views\Email\Settings\Senders;
use \packages\base\Translator;
use \packages\userpanel;
use \packages\email\Views\Settings\Senders\Delete as DeleteView;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;

class Delete extends DeleteView{
	use ViewTrait;

	public function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.senders.delete"));
		Navigation::active("settings/email/senders");
	}
}
