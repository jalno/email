<?php
namespace themes\clipone\Views\Email\Settings\Senders;
use \packages\base\Translator;
use \packages\base\Events;
use \packages\base\Frontend\Theme;

use \packages\userpanel;
use \packages\email\Sender;
use \packages\email\Views\Settings\Senders\Add as AddView;

use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Views\FormTrait;

class Add extends AddView{
	use ViewTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans("settings.email.senders.add"));
		$this->setNavigation();
		$this->addBodyClass('email_senders');
	}
	private function setNavigation(){
		$add = new Navigation\MenuItem("sender_add");
		$add->setTitle(Translator::trans('add'));
		$add->setIcon('fa fa-plus');
		$add->setURL(userpanel\url('settings/email/senders/add'));
		//breadcrumb::addItem($add);
		navigation::active("settings/email/senders");
	}
	public function getsendersForSelect(){
		$options = array();
		foreach($this->getsenders()->get() as $sender){
			$title = Translator::trans('email.sender.'.$sender->getName());
			$options[] = array(
				'value' => $sender->getName(),
				'title' => $title ? $title : $sender->getName()
			);
		}
		return $options;
	}
	public function getsenderStatusForSelect(){
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
}
