<?php
namespace themes\clipone\Views\Email;
use \packages\base\Frontend\Theme;
use \packages\base\Translator;
use \packages\userpanel;
use \packages\email\Views\Send as EmailEnd;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Navigation\MenuItem;
use \themes\clipone\ViewTrait;
use \themes\clipone\Views\FormTrait;

class Send extends EmailEnd{
	use ViewTrait,FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans('email.send'));
		$this->setNavigation();
		$this->addBodyClass('emailsend');
		$this->addJSFile(Theme::url('assets/plugins/ckeditor/ckeditor.js'));
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
		$item = new MenuItem("email");
		$item->setTitle(Translator::trans('emailes'));
		$item->setIcon('fa fa-envelope');
		Breadcrumb::addItem($item);

		$item = new MenuItem("send");
		$item->setTitle(Translator::trans('email.send'));
		$item->setURL(userpanel\url('email/send'));
		Breadcrumb::addItem($item);
		Navigation::active("email/send");
	}
}
