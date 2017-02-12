<?php
namespace packages\email\views;
use \packages\email\views\form;
class send extends form{
	public function setAddresses($addresses){
		$this->setData($addresses,'addresses');
	}
	protected function getAddresses(){
		return $this->getData('addresses');
	}
}
