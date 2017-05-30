<?php
namespace themes\clipone\views\email\settings\templates;
use \packages\base\translator;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \packages\email\template;
use \packages\email\views\settings\templates\edit as editView;

use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\views\formTrait;

class edit extends editView{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.templates.edit"));
		$this->setNavigation();
		$this->addAssets();
		$this->addBodyClass('email_templates');
	}
	public function addAssets(){
		$this->addJSFile(theme::url('assets/plugins/ckeditor/ckeditor.js'));
	}
	private function setNavigation(){
		navigation::active("settings/email/templates");
	}
	public function getTemplatesForSelect(){
		$options = array();
		$formname = $this->getDataForm('name');
		$found = false;
		foreach($this->getTemplates() as $template){
			if($template->name == $formname){
				$found = true;
			}
			$title = translator::trans('email.template.name.'.$template->name);
			$variables = array();
			foreach($template->variables as $variable){
				$description = '';
				$name = explode("->", $variable);
				for($x=0;$x!=count($name) and !$description;$x++){
					$variable_name = implode('->', array_slice($name,$x));
					$description = translator::trans('email.template.variable.'.$variable_name);
				}
				$variables[] = array(
					'key' => $variable,
					'description' => (string)$description
				);
			}
			$options[] = array(
				'value' => $template->name,
				'title' => $title ? $title : $template->name,
				'data' => array(
					'variables' => $variables
				)
			);
		}
		if(!$found){
			array_unshift($options,array(
				'value' => $formname,
				'title' => $formname
			));
		}
		return $options;
	}
	public function getTemplateStatusForSelect(){
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
	public function getLanguagesForSelect(){
		$options = array();
		foreach(translator::$allowlangs as $lang){
			$options[] = array(
				'title' => translator::trans('translations.langs.'.$lang),
				'value' => $lang
			);
		}
		return $options;
	}
}
