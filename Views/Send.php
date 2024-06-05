<?php
namespace packages\email\Views;
use \packages\email\Views\Form;
class Send extends Form{
	public function setAddresses($addresses){
		$this->setData($addresses,'addresses');
	}
	protected function getAddresses(){
		return $this->getData('addresses');
	}
}
