<?php
namespace themes\clipone\Views\Email\Get;
use \packages\base\Translator;
use \packages\base\Frontend\Theme;
use \packages\userpanel;
use \packages\userpanel\User;
use \packages\email\Get;
use \packages\email\Views\Get\ListView as GetList;
use \themes\clipone\Navigation;
use \themes\clipone\Navigation\MenuItem;
use \themes\clipone\Views\ListTrait;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\ViewTrait;

class ListView extends GetList{
	use ViewTrait,ListTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans('email.get'));
		Navigation::active("email/get");
		$this->addBodyClass('emaillist');
		$this->setUserInput();
	}
	protected function getStatusForSelect(){
		return array(
			array(
				'title' => Translator::trans("choose"),
				'value' => ''
			),
			array(
				'title' => Translator::trans("email.get.status.unread"),
				'value' => Get::unread
			),
			array(
				'title' => Translator::trans("email.get.status.read"),
				'value' => Get::read
			)
		);
	}
	public function getComparisonsForSelect(){
		return array(
			array(
				'title' => Translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => Translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => Translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
	private function setUserInput(){
		if($error = $this->getFormErrorsByInput('sender_user')){
			$error->setInput('sender_user_name');
			$this->setFormError($error);
		}
		$user = $this->getDataForm('sender_user');
		if($user and $user = User::byId($user)){
			$this->setDataForm($user->name, 'sender_user_name');
		}
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if(!$email = Navigation::getByName('email')){
				$email = new MenuItem("email");
				$email->setTitle(Translator::trans('emailes'));
				$email->setIcon('fa fa-envelope');
				Navigation::addItem($email);
			}
			$get = new MenuItem("get");
			$get->setTitle(Translator::trans('email.get'));
			$get->setURL(userpanel\url('email/get'));
			$get->setIcon('clip-download');
			$email->addItem($get);
		}
	}
}
