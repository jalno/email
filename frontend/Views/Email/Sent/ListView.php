<?php
namespace themes\clipone\Views\Email\Sent;
use \packages\base\Translator;
use \packages\base\Frontend\Theme;
use \packages\userpanel;
use \packages\userpanel\User;
use \packages\email\Sent;
use \packages\email\Views\Sent\ListView as SentList;
use \themes\clipone\Navigation;
use \themes\clipone\Navigation\MenuItem;
use \themes\clipone\Views\ListTrait;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\ViewTrait;

class ListView extends SentList{
	use ViewTrait,ListTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle(Translator::trans('email.sent'));
		navigation::active("email/sent");
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
				'title' => Translator::trans("email.sent.status.queued"),
				'value' => Sent::queued
			),
			array(
				'title' => Translator::trans("email.sent.status.sending"),
				'value' => Sent::sending
			),
			array(
				'title' => Translator::trans("email.sent.status.sent"),
				'value' => Sent::sent
			),
			array(
				'title' => Translator::trans("email.sent.status.failed"),
				'value' => Sent::failed
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
		foreach(array('sender_user', 'receiver_user') as $field){
			if($error = $this->getFormErrorsByInput($field)){
				$error->setInput($field.'_name');
				$this->setFormError($error);
			}
			$user = $this->getDataForm($field);
			if($user and $user = User::byId($user)){
				$this->setDataForm($user->name, $field.'_name');
			}
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
			$sent = new MenuItem("sent");
			$sent->setTitle(Translator::trans('email.sent'));
			$sent->setURL(userpanel\url('email/sent'));
			$sent->setIcon('clip-upload');
			$email->addItem($sent);
		}
	}
}
