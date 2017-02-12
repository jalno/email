<?php
namespace packages\email\views\settings\senders;
use \packages\email\events\senders;
use \packages\userpanel\views\form;
class add extends form{
	public function setsenders(senders $senders){
		$this->setData($senders, "senders");
	}
	protected function getsenders(){
		return $this->getData('senders');
	}
}
