<?php
namespace packages\email\views\settings\receivers;
use \packages\email\receiver;
use \packages\userpanel\views\form;
class delete extends form{
	public function setReceiver(receiver $receiver){
		$this->setData($receiver, "receiver");
	}
	protected function getReceiver(){
		return $this->getData('receiver');
	}
}
