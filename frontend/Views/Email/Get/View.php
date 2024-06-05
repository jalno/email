<?php
namespace themes\clipone\Views\Email\Get;
use \themes\clipone\ViewTrait;
use \packages\email\Views\Get\View as GetView;
class View extends GetView{
	use ViewTrait;
	protected $email;
	function __beforeLoad(){
		$this->email = $this->getEmail();
	}
}
