<?php
namespace themes\clipone\views\email\settings\receivers;
use \packages\base\translator;
use \packages\base\events;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \packages\email\receiver;
use \packages\email\views\settings\receivers\edit as editView;

use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\views\formTrait;

class edit extends editView{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.receivers.edit"));
		$this->setNavigation();
		$this->editAssets();
	}
	public function editAssets(){

	}
	private function setNavigation(){
		$edit = new navigation\menuItem("receiver_edit");
		$edit->setTitle(translator::trans('edit'));
		$edit->setIcon('fa fa-plus');
		$edit->setURL(userpanel\url('settings/email/receivers/edit'));
		//breadcrumb::editItem($edit);
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
