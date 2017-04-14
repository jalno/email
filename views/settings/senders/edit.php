<?php
namespace packages\email\views\settings\senders;
use \packages\email\sender;
use \packages\email\events\senders;
use \packages\userpanel\views\form;
class edit extends form{
	public function setSenders($senders){
		$this->setData($senders, "senders");
	}
	protected function getSenders(){
		return $this->getData('senders');
	}
	public function setSender(sender $sender){
		$this->setData($sender, "sender");
		$this->setDataForm($sender->toArray());
		foreach($sender->params as $param){
			$this->setDataForm($param->value, $param->name);
		}
		foreach($this->getSenders() as $s){
			if($s->getHandler() == $sender->handler){
				$this->setDataForm($s->getName(), "sender");
				break;
			}
		}
	}
	protected function getSender(){
		return $this->getData('sender');
	}
}
