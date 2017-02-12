<?php
namespace themes\clipone\views\email;
use \packages\base\frontend\theme;
use \packages\base\translator;
use \packages\userpanel;
use \packages\email\views\send as emailend;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;

class send extends emailend{
	use viewTrait,formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans('email.send'));
		$this->setNavigation();
		$this->addJSFile(theme::url('assets/plugins/ckeditor/ckeditor.js'));
		$this->addJSFile(theme::url('assets/js/pages/send.js'));
		$this->addCSSFile(theme::url('assets/css/pages/send.css'));
	}
	protected function getNumbersForSelect(){
		$options = array();
		foreach($this->getNumbers() as $number){
			$options[] = array(
				'title' => $number->number,
				'value' => $number->id
			);
		}
		return $options;
	}
	protected function getAddressesForSelect(){
		$options = array();
		foreach($this->getAddresses() as $address){
			$options[] = array(
				'title' => $address->name.' <'.$address->address.'>',
				'value' => $address->id
			);
		}
		return $options;
	}
	protected function setNavigation(){
		$item = new menuItem("email");
		$item->setTitle(translator::trans('emailes'));
		$item->setIcon('fa fa-envelope');
		breadcrumb::addItem($item);

		$item = new menuItem("send");
		$item->setTitle(translator::trans('email.send'));
		$item->setURL(userpanel\url('email/send'));
		breadcrumb::addItem($item);
		navigation::active("email/send");
	}
}
