<?php
namespace themes\clipone\views\email\settings\senders;
use \packages\base\translator;
use \packages\base\events;
use \packages\base\frontend\theme;
use \packages\base\options;

use \packages\userpanel;
use \packages\email\sender;
use \packages\email\views\settings\senders\edit as editView;

use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\views\formTrait;

class edit extends editView{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.senders.edit"));
		$this->setNavigation();
		$this->addBodyClass('email_senders');
	}
	private function setNavigation(){
		navigation::active("settings/email/senders");
	}
	public function getSendersForSelect(){
		$options = array();
		foreach($this->getSenders() as $sender){
			$title = translator::trans('email.sender.'.$sender->getName());
			$options[] = array(
				'value' => $sender->getName(),
				'title' => $title ? $title : $sender->getName()
			);
		}
		return $options;
	}
	public function getsenderstatusForSelect(){
		$options = array(
			array(
				'title' => translator::trans('email.sender.status.active'),
				'value' => sender::active
			),
			array(
				'title' => translator::trans('email.sender.status.deactive'),
				'value' => sender::deactive
			)
		);
		return $options;
	}
	protected function getAddressesData(){
		$addresses = array();
		foreach($this->getsender()->addresses as $address){
			$addressData = $address->toArray();
			if(options::get('packages.email.defaultAddress') == $address->id){
				$addressData['primary'] = true;
			}
			$addresses[] = $addressData;
		}
		return $addresses;
	}
}
