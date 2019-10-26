<?php
namespace themes\clipone\views\email\get;
use \themes\clipone\viewTrait;
use \packages\email\views\get\view as getView;
class view extends getView{
	use viewTrait;
	protected $email;
	function __beforeLoad(){
		$this->email = $this->getEmail();
	}
}
