<?php
namespace themes\clipone\views\email\settings\senders;
use \packages\base\translator;
use \packages\userpanel;
use \packages\email\views\settings\senders\delete as deleteView;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;

class delete extends deleteView{
	use viewTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans("settings.email.senders.delete"));
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
}
