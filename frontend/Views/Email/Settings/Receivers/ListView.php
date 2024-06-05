<?php
namespace themes\clipone\Views\Email\Settings\Receivers;

use \packages\base\Translator;
use \packages\base\Frontend\Theme;

use \packages\userpanel;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Views\ListTrait;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\Navigation\MenuItem;

use \packages\email\Views\Settings\Receivers\ListView as ReceiversListview;
use \packages\email\Receiver;

class ListView extends ReceiversListview{
	use ViewTrait, ListTrait, FormTrait;
	private $categories;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.receivers"));
		Navigation::active("settings/email/receivers");
		$this->setButtons();
		$this->addAssets();
	}
	private function addAssets(){

	}
	public function getComparisonsForSelect(){
		return array(
			array(
				'title' => Translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => Translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => Translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			$settings = navigation::getByName("settings");
			if(!$email = Navigation::getByName("settings/email")){
				$email = new MenuItem("email");
				$email->setTitle(Translator::trans('settings.email'));
				$email->setIcon("fa fa-envelope");
				if($settings)$settings->addItem($email);
			}
			$receivers = new MenuItem("receivers");
			$receivers->setTitle(Translator::trans('settings.email.receivers'));
			$receivers->setURL(userpanel\url('settings/email/receivers'));
			$receivers->setIcon('fa fa-angle-double-down');
			$email->addItem($receivers);
		}
	}
	public function setButtons(){
		$this->setButton('edit', $this->canEdit, array(
			'title' => Translator::trans('edit'),
			'icon' => 'fa fa-edit',
			'classes' => array('btn', 'btn-xs', 'btn-teal')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => Translator::trans('delete'),
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
	public function getStatusForSelect(){
		return array(
			array(
				'title' => '',
				'value' => ''
			),
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
}
