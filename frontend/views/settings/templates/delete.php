<?php
namespace themes\clipone\views\email\settings\templates;
use \packages\base\translator;
use \packages\userpanel;
use \packages\email\views\settings\templates\delete as deleteView;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;

class delete extends deleteView{
	use viewTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.templates.delete"));
		navigation::active("settings/email/templates");
	}
}
