<?php
namespace themes\clipone\views\email\sent;
use \packages\base\translator;
use \packages\base\frontend\theme;
use \packages\userpanel;
use \packages\userpanel\user;
use \packages\email\sent;
use \packages\email\views\sent\listview as sentList;
use \themes\clipone\navigation;
use \themes\clipone\navigation\menuItem;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;

class listview extends sentList{
	use viewTrait,listTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(translator::trans('email.sent'));
		navigation::active("email/sent");
		$this->addBodyClass('emaillist');
		$this->setUserInput();
	}
	protected function getStatusForSelect(){
		return array(
			array(
				'title' => translator::trans("choose"),
				'value' => ''
			),
			array(
				'title' => translator::trans("email.sent.status.queued"),
				'value' => sent::queued
			),
			array(
				'title' => translator::trans("email.sent.status.sending"),
				'value' => sent::sending
			),
			array(
				'title' => translator::trans("email.sent.status.sent"),
				'value' => sent::sent
			),
			array(
				'title' => translator::trans("email.sent.status.failed"),
				'value' => sent::failed
			)
		);
	}
	public function getComparisonsForSelect(){
		return array(
			array(
				'title' => translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => translator::trans('search.comparison.startswith'),
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
			if($user and $user = user::byId($user)){
				$this->setDataForm($user->name, $field.'_name');
			}
		}
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if(!$email = navigation::getByName('email')){
				$email = new menuItem("email");
				$email->setTitle(translator::trans('emailes'));
				$email->setIcon('fa fa-envelope');
				navigation::addItem($email);
			}
			$sent = new menuItem("sent");
			$sent->setTitle(translator::trans('email.sent'));
			$sent->setURL(userpanel\url('email/sent'));
			$sent->setIcon('clip-upload');
			$email->addItem($sent);
		}
	}
}
