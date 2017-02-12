<?php
namespace packages\email\views\settings\receivers;
use \packages\email\receiver;
use \packages\userpanel\views\form;
class edit extends form{
	public function setReceiver(receiver $receiver){
		$this->setData($receiver, "receiver");
		$this->setDataForm($receiver->toArray());
	}
	protected function getReceiver(){
		return $this->getData('receiver');
	}
}
