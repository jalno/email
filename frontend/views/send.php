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
		$this->addBodyClass('emailsend');
		$this->addJSFile(theme::url('assets/plugins/ckeditor/ckeditor.js'));
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
