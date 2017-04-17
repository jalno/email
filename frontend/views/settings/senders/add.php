<?php
namespace themes\clipone\views\email\settings\senders;
use \packages\base\translator;
use \packages\base\events;
use \packages\base\frontend\theme;

use \packages\userpanel;
use \packages\email\sender;
use \packages\email\views\settings\senders\add as addView;

use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\views\formTrait;

class add extends addView{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.senders.add"));
		$this->setNavigation();
		$this->addBodyClass('email_senders');
	}
	private function setNavigation(){
		$add = new navigation\menuItem("sender_add");
		$add->setTitle(translator::trans('add'));
		$add->setIcon('fa fa-plus');
		$add->setURL(userpanel\url('settings/email/senders/add'));
		//breadcrumb::addItem($add);
		navigation::active("settings/email/senders");
	}
	public function getsendersForSelect(){
		$options = array();
		foreach($this->getsenders()->get() as $sender){
			$title = translator::trans('email.sender.'.$sender->getName());
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
}
