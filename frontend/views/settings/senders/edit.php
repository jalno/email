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
		$this->addAssets();
	}
	public function addAssets(){
		$this->addJSFile(theme::url('assets/plugins/jquery-validation/dist/jquery.validate.min.js'));
		$this->addJSFile(theme::url('assets/plugins/bootstrap-inputmsg/bootstrap-inputmsg.min.js'));
		$this->addJSFile(theme::url('assets/js/pages/senders.js'));
		$this->addCSSFile(theme::url('assets/css/pages/senders.css'));
	}
	private function setNavigation(){
		navigation::active("settings/email/senders");
	}
	public function getSendersForSelect(){
		$options = array();
		foreach($this->getSenders()->get() as $sender){
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
