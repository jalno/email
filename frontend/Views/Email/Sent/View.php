<?php
namespace themes\clipone\Views\Email\Sent;
use \themes\clipone\ViewTrait;
use \packages\email\Views\Sent\View as SentView;
class View extends SentView{
	use ViewTrait;
	protected $email;
	function __beforeLoad(){
		$this->email = $this->getEmail();
	}
}
