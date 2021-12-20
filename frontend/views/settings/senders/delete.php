<?php
namespace themes\clipone\views\email\settings\senders;
use \packages\base\translator;
use \packages\userpanel;
use \packages\email\views\settings\senders\delete as deleteView;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;

class delete extends deleteView{
	use viewTrait;

	public function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.senders.delete"));
		navigation::active("settings/email/senders");
	}
}
