<?php
namespace themes\clipone\views\email\settings\receivers;
use \packages\base\translator;
use \packages\email\views\settings\receivers\delete as deleteView;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
class delete extends deleteView{
	use viewTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.receivers.delete"));
		navigation::active("settings/email/receivers");
	}
}
