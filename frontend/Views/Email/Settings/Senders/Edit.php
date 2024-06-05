<?php
namespace themes\clipone\Views\Email\Settings\Senders;
use \packages\base\Translator;
use \packages\base\Events;
use \packages\base\Frontend\Theme;
use \packages\base\Options;

use \packages\userpanel;
use \packages\email\Sender;
use \packages\email\Views\Settings\Senders\Edit as EditView;

use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Views\FormTrait;

class Edit extends EditView{
	use ViewTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.senders.edit"));
		$this->setNavigation();
		$this->addBodyClass('email_senders');
	}
	private function setNavigation(){
		Navigation::active("settings/email/senders");
	}
	public function getSendersForSelect(){
		$options = array();
		foreach($this->getSenders() as $sender){
			$title = Translator::trans('email.sender.'.$sender->getName());
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
				'title' => Translator::trans('email.sender.status.active'),
				'value' => Sender::active
			),
			array(
				'title' => Translator::trans('email.sender.status.deactive'),
				'value' => Sender::deactive
			)
		);
		return $options;
	}
	protected function getAddressesData(){
		$addresses = array();
		foreach($this->getsender()->addresses as $address){
			$addressData = $address->toArray();
			if(Options::get('packages.email.defaultAddress') == $address->id){
				$addressData['primary'] = true;
			}
			$addresses[] = $addressData;
		}
		return $addresses;
	}
}
