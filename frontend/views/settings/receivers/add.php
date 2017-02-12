<?php
namespace themes\clipone\views\email\settings\receivers;
use \packages\base\translator;
use \packages\base\events;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \packages\email\receiver;
use \packages\email\views\settings\receivers\add as addView;

use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\views\formTrait;

class add extends addView{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.receivers.add"));
		$this->setNavigation();
		$this->addAssets();
	}
	public function addAssets(){
		
	}
	private function setNavigation(){
		$add = new navigation\menuItem("receiver_add");
		$add->setTitle(translator::trans('add'));
		$add->setIcon('fa fa-plus');
		$add->setURL(userpanel\url('settings/email/receivers/add'));
		//breadcrumb::addItem($add);
		navigation::active("settings/email/receivers");
	}
	public function getStatusForSelect(){
		return array(
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
	public function getTypesForSelect(){
		return array(
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
}
