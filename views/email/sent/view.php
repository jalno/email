<?php
namespace packages\email\views\sent;
use \packages\email\sent as email;
class view extends \packages\email\view{
	public function setEmail(email $email){
		$this->setData($email, "email");
	}
	protected function getEmail():email{
		return $this->getData("email");
	}
}
