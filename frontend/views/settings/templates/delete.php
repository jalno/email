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
	public function gettemplatesForSelect(){
		$options = array();
		foreach($this->gettemplates()->get() as $template){
			$title = translator::trans('email.template.'.$template->getName());
			$options[] = array(
				'value' => $template->getName(),
				'title' => $title ? $title : $template->getName()
			);
		}
		return $options;
	}
	public function gettemplateStatusForSelect(){
		$options = array(
			array(
				'title' => translator::trans('email.template.status.active'),
				'value' => template::active
			),
			array(
				'title' => translator::trans('email.template.status.deactive'),
				'value' => template::deactive
			)
		);
		return $options;
	}
}
