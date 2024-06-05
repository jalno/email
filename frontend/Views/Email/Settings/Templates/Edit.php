<?php
namespace themes\clipone\Views\Email\Settings\Templates;
use \packages\base\Translator;
use \packages\base\Frontend\Theme;

use \packages\userpanel;
use \packages\email\Template;
use \packages\email\Views\Settings\Templates\Edit as EditView;

use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Views\FormTrait;

class Edit extends EditView{
	use ViewTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.templates.edit"));
		$this->setNavigation();
		$this->addAssets();
		$this->addBodyClass('email_templates');
	}
	public function addAssets(){
		$this->addJSFile(Theme::url('assets/plugins/ckeditor/ckeditor.js'));
	}
	private function setNavigation(){
		Navigation::active("settings/email/templates");
	}
	public function getTemplatesForSelect(){
		$options = array();
		$formname = $this->getDataForm('name');
		$found = false;
		foreach($this->getTemplates() as $template){
			if($template->name == $formname){
				$found = true;
			}
			$title = Translator::trans('email.template.name.'.$template->name);
			$variables = array();
			foreach($template->variables as $variable){
				$description = '';
				$name = explode("->", $variable);
				for($x=0;$x!=count($name) and !$description;$x++){
					$variable_name = implode('->', array_slice($name,$x));
					$description = Translator::trans('email.template.variable.'.$variable_name);
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
				'title' => Translator::trans('email.template.status.active'),
				'value' => Template::active
			),
			array(
				'title' => Translator::trans('email.template.status.deactive'),
				'value' => Template::deactive
			)
		);
		return $options;
	}
	public function getLanguagesForSelect(){
		$options = array();
		foreach(Translator::$allowlangs as $lang){
			$options[] = array(
				'title' => Translator::trans('translations.langs.'.$lang),
				'value' => $lang
			);
		}
		return $options;
	}
}
