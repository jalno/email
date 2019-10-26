<?php
namespace themes\clipone\views\email\sent;
use \themes\clipone\viewTrait;
use \packages\email\views\sent\view as sentView;
class view extends sentView{
	use viewTrait;
	protected $email;
	function __beforeLoad(){
		$this->email = $this->getEmail();
	}
}
