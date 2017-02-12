<?php
namespace themes\clipone\views\email\settings\senders;

use \packages\base\translator;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation\menuItem;

use \packages\email\views\settings\senders\listview as sendersListview;
use \packages\email\sender;

class listview extends sendersListview{
	use viewTrait, listTrait, formTrait;
	private $categories;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.senders"));
		navigation::active("settings/email/senders");
		$this->setButtons();
		$this->addAssets();
	}
	private function addAssets(){

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
			$senders = new menuItem("senders");
			$senders->setTitle(translator::trans('settings.email.senders'));
			$senders->setURL(userpanel\url('settings/email/senders'));
			$senders->setIcon('fa fa-rss');
			$email->addItem($senders);
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
