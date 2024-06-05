<?php
namespace themes\clipone\Views\Email\Settings\Receivers;
use \packages\base\Translator;
use \packages\base\Events;
use \packages\base\Frontend\Theme;

use \packages\userpanel;
use \packages\email\Receiver;
use \packages\email\Views\Settings\Receivers\Edit as EditView;

use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Views\FormTrait;

class Edit extends EditView{
	use ViewTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.receivers.edit"));
		$this->setNavigation();
		$this->editAssets();
	}
	public function editAssets(){

	}
	private function setNavigation(){
		$edit = new Navigation\MenuItem("receiver_edit");
		$edit->setTitle(Translator::trans('edit'));
		$edit->setIcon('fa fa-plus');
		$edit->setURL(userpanel\url('settings/email/receivers/edit'));
		//breadcrumb::editItem($edit);
		Navigation::active("settings/email/receivers");
	}
	public function getStatusForSelect(){
		return array(
			array(
				'title' => Translator::trans('email.receiver.status.active'),
				'value' => Receiver::active
			),
			array(
				'title' => Translator::trans('email.receiver.status.deactive'),
				'value' => Receiver::deactive
			)
		);
	}
	public function getTypesForSelect(){
		return array(
			array(
				'title' => "IMAP",
				'value' => Receiver::IMAP
			),
			array(
				'title' => "POP3",
				'value' => Receiver::POP3
			),
			array(
				'title' => "NNTP",
				'value' => Receiver::NNTP
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
				'value' => Receiver::SSL
			),
			array(
				'title' => "TLS",
				'value' => Receiver::TLS
			)
		);
	}
}
