<?php
namespace themes\clipone\views\email\settings\templates;

use \packages\base\translator;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation\menuItem;

use \packages\email\views\settings\templates\listview as templatesListview;
use \packages\email\template;

class listview extends templatesListview{
	use viewTrait, listTrait, formTrait;
	private $categories;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.templates"));
		navigation::active("settings/email/templates");
		$this->setButtons();
		$this->addAssets();
	}
	private function addAssets(){

	}
	public function getTemplateStatusForSelect(){
		$options = array(
			array(
				'title' => '',
				'value' => ''
			),
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
	public function getComparisonsForSelect(){
		return array(
			array(
				'title' => translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			$settings = navigation::getByName("settings");
			if(!$email = navigation::getByName("settings/email")){
				$email = new menuItem("email");
				$email->setTitle(translator::trans('settings.email'));
				$email->setIcon("fa fa-envelope");
				if($settings)$settings->addItem($email);
			}
			$templates = new menuItem("templates");
			$templates->setTitle(translator::trans('settings.email.templates'));
			$templates->setURL(userpanel\url('settings/email/templates'));
			$templates->setIcon('fa fa-file-text-o');
			$email->addItem($templates);
		}
	}
	public function setButtons(){
		$this->setButton('edit', $this->canEdit, array(
			'title' => translator::trans('edit'),
			'icon' => 'fa fa-edit',
			'classes' => array('btn', 'btn-xs', 'btn-teal')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => translator::trans('delete'),
			'icon' => 'fa fa-times',
			'classes' => array('btn', 'btn-xs', 'btn-bricky')
		));
	}
}
