<?php
namespace packages\email\views\settings\senders;
use \packages\email\sender;
use \packages\userpanel\views\form;
class delete extends form{
	public function setSender(sender $sender){
		$this->setData($sender, "sender");
	}
	protected function getSender(){
		return $this->getData('sender');
	}
}
