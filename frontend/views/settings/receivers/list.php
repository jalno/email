<?php
namespace themes\clipone\views\email\settings\receivers;

use \packages\base\translator;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation\menuItem;

use \packages\email\views\settings\receivers\listview as receiversListview;
use \packages\email\receiver;

class listview extends receiversListview{
	use viewTrait, listTrait, formTrait;
	private $categories;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.receivers"));
		navigation::active("settings/email/receivers");
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
			$receivers = new menuItem("receivers");
			$receivers->setTitle(translator::trans('settings.email.receivers'));
			$receivers->setURL(userpanel\url('settings/email/receivers'));
			$receivers->setIcon('fa fa-angle-double-down');
			$email->addItem($receivers);
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
	public function getTypesForSelect(){
		return array(
			array(
				'title' => '',
				'value' => ''
			),
			array(
				'title' => "IMAP",
				'value' => receiver::IMAP
			),
			array(
				'title' => "POP3",
				'value' => receiver::POP3
			),
			array(
				'title' => "NNTP",
				'value' => receiver::NNTP
			)
		);
	}
	public function getEncryptionsForSelect(){
		return array(
			array(
				'title' => '',
				'value' => ''
			),
			array(
				'title' => "SSL",
				'value' => receiver::SSL
			),
			array(
				'title' => "TLS",
				'value' => receiver::TLS
			)
		);
	}
	public function getStatusForSelect(){
		return array(
			array(
				'title' => '',
				'value' => ''
			),
			array(
				'title' => translator::trans('email.receiver.status.active'),
				'value' => receiver::active
			),
			array(
				'title' => translator::trans('email.receiver.status.deactive'),
				'value' => receiver::deactive
			)
		);
	}
}
