<?php
namespace packages\email\controllers;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\db\parenthesis;
use \packages\base\views\FormError;
use \packages\base\view\error;
use \packages\base\inputValidation;
use \packages\base\utility\safe;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \packages\email\view;
use \packages\email\authentication;
use \packages\email\controller;
use \packages\email\authorization;
use \packages\email\sent;
use \packages\email\get;
use \packages\email\sender;
use \packages\email\sender\address;
use \packages\email\api;
use \packages\email\views;

class email extends controller{
	protected $authentication = true;
	public function sent(){
		authorization::haveOrFail('sent_list');
		$view = view::byName("\\packages\\email\\views\\sent\\listview");
		$types = authorization::childrenTypes();
		$sent_list_anonymous = authorization::is_accessed('sent_list_anonymous');
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'sender_user' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'sender_number' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'receiver_user' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'receiver_number' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'text' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'status' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		);
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['status']) and $inputs['status'] != 0){
				if(!in_array($inputs['status'], array(sent::queued, sent::sending, sent::sent,sent::failed))){
					throw new inputValidation("status");
				}
			}
			foreach(array('sender_user', 'receiver_user') as $field){
				if(isset($inputs[$field]) and $inputs[$field] != 0){
					$user = user::byId($inputs[$field]);
					if(!$user){
						throw new inputValidation($field);
					}
					$inputs[$field] = $user->id;
				}
			}

			foreach(array('id', 'sender_user', 'receiver_user', 'sender_number', 'receiver_number', 'text', 'status') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id', 'status', 'sender_user', 'receiver_user'))){
						$comparison = 'equals';
					}
					db::where("email_sent.".$item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new parenthesis();
				foreach(array('sender_number', 'receiver_number', 'text') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where("email_sent.".$item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				db::where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputs));
		if($sent_list_anonymous){
			db::join("userpanel_users", "userpanel_users.id=email_sent.receiver_user", "left");
			$parenthesis = new parenthesis();
			$parenthesis->where("userpanel_users.type",  $types, 'in');
			$parenthesis->where("email_sent.receiver_user", null, 'is','or');
			db::where($parenthesis);
		}else{
			db::join("userpanel_users", "userpanel_users.id=email_sent.receiver_user", "inner");
			if($types){
				db::where("userpanel_users.type", $types, 'in');
			}else{
				db::where("userpanel_users.id", authentication::getID());
			}
		}
		db::orderBy('email_sent.id', ' DESC');
		db::pageLimit($this->items_per_page);
		$items = db::paginate('email_sent', $this->page, array("email_sent.*"));
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$sents = array();
		foreach($items  as $item){
			$sents[] = new sent($item);
		}
		$view->setDataList($sents);

		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function get($name){
		authorization::haveOrFail('get_list');
		$view = view::byName("\\packages\\email\\views\\get\\listview");
		$types = authorization::childrenTypes();
		$get_list_anonymous = authorization::is_accessed('get_list_anonymous');
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'sender_user' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'sender_number' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'receiver_number' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'text' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'status' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		);
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['status']) and $inputs['status'] != 0){
				if(!in_array($inputs['status'], array(get::unread, get::read))){
					throw new inputValidation("status");
				}
			}
			if(isset($inputs['sender_user']) and $inputs['sender_user'] != 0){
				$user = user::byId($inputs['sender_user']);
				if(!$user){
					throw new inputValidation('sender_user');
				}
				$inputs['sender_user'] = $user->id;
			}

			foreach(array('id', 'sender_user', 'sender_number', 'receiver_number', 'text', 'status') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id', 'status', 'sender_user'))){
						$comparison = 'equals';
					}
					db::where("email_get.".$item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new parenthesis();
				foreach(array('sender_number', 'receiver_number', 'text') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where("email_get.".$item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				db::where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputs));
		if($get_list_anonymous){
			db::join("userpanel_users", "userpanel_users.id=email_get.sender_user", "left");
			$parenthesis = new parenthesis();
			$parenthesis->where("userpanel_users.type",  $types, 'in');
			$parenthesis->where("email_get.sender_user", null, 'is','or');
			db::where($parenthesis);
		}else{
			db::join("userpanel_users", "userpanel_users.id=email_get.sender_user", "inner");
			if($types){
				db::where("userpanel_users.type", $types, 'in');
			}else{
				db::where("userpanel_users.id", authentication::getID());
			}
		}
		db::orderBy('email_get.id', ' DESC');
		db::pageLimit($this->items_per_page);
		$items = db::paginate('email_get', $this->page, array("email_get.*"));
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$gets = array();
		foreach($items  as $item){
			$gets[] = new get($item);
		}
		$view->setDataList($gets);

		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function send(){
		$view = view::byName("\\packages\\email\\views\\send");
		authorization::haveOrFail('send');
		db::join("email_senders", "email_senders_addresses.sender=email_senders.id", "inner");
		db::where("email_senders.status", sender::active);
		db::where("email_senders_addresses.status", address::active);
		$addressesData = db::get("email_senders_addresses", null, "email_senders_addresses.*");
		$addresses = array();
		foreach($addressesData as $data){
			$addresses[] = new address($data);
		}

		$view->setAddresses($addresses);
		if(http::is_post()){

			$this->response->setStatus(false);
			$inputsRules = array(
				'to' => array(),
				'from' => array(
					'type' => 'number',
					'optional' => true
				),
				'subject' => array(
					'type' => 'string'
				),
				'html' => array(),
				'attachments' => array(
					'type' => 'file',
					'optional' => true,
					'empty' => true
				)
			);
			try {
				$inputs = $this->checkinputs($inputsRules);

				if(array_key_exists('from',$inputs)){
					if(!$inputs['from'] = address::byId($inputs['from'])){
						throw new inputValidation("from");
					}
					if($inputs['from']->status != address::active or $inputs['from']->sender->status != address::active){
						throw new inputValidation('from');
					}
				}
				$inputs['to'] = explode(',',$inputs['to']);
				foreach($inputs['to'] as $key => $to){
					$name = null;
					$email = '';
					$to = trim($to);
					if(strpos($to, '<') !== false){
						if(preg_match('/(.+)\\s*\\<\\s*(.+)\\s*\\>/', $to, $matches)){
							$matches[1] = safe::string($matches[1]);
							$matches[2] = safe::string($matches[2]);
							if(!$matches[1]){
								throw new inputValidation('to');
							}
							if(!safe::is_email($matches[2])){
								throw new inputValidation('to');
							}
							$name = $matches[1];
							$email = $matches[2];
						}else{
							throw new inputValidation('to');
						}
					}elseif(safe::is_email($to)){
						$email = $to;
					}else{
						throw new inputValidation('to');
					}
					$inputs['to'][$key] = array(
						'name' => $name,
						'email' => $email
					);
				}
				if(isset($inputs['attachments'])){
					foreach($inputs['attachments'] as $key => $attachment){
						if($attachment['error'] == 0){

						}elseif(isset($attachment['error']) and $attachment['error'] != 4){
							throw new inputValidation("attachments[{$key}]");
						}
					}

				}
				$sendone = false;
				foreach($inputs['to'] as $receiver){
					$email = new api;
					$email->to($receiver['email'],$receiver['name']);
					$email->fromUser(authentication::getUser());
					$email->subject($inputs['subject']);
					$email->html($inputs['html']);
					if(array_key_exists('from',$inputs)){
						$email->fromAddress($inputs['from']);
					}
					$email->now();
					if($email->send() == sent::sent){
						$sendone = true;
					}
				}
				if($sendone){
					$this->response->setStatus(true);
					$this->response->Go(userpanel\url('email/sent'));
				}else{
					throw new sendException();
				}
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(sendException $error){
				$error = new error();
				$error->setCode('email.send');
				$view->addError($error);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
			$inputsRules = [
				'user' => [
					'type' => 'number',
					'optional' => true
				],
				'to' => [
					'type' => 'email',
					'optional' => true
				],
				'forward' => [
					'type' => 'number',
					'optional' => true
				],
				'type' => [
					'values' => ['get', 'sent'],
					'optional' => true
				]
			];
			$inputs = $this->checkinputs($inputsRules);
			foreach(array_keys($inputsRules) as $item){
				if(isset($inputs[$item]) and $inputs[$item] == ""){
					unset($inputs[$item]);
				}
			}
			if(isset($inputs['user'])){
				if($user = user::byId($inputs['user'])){
					$view->setDataForm($user->email, 'to');
				}
			}elseif(isset($inputs['to'])){
				$view->setDataForm($inputs['to'], 'to');
			}
			if(isset($inputs['forward'])){
				if(isset($inputs['type'])){
					$types = authorization::childrenTypes();
					switch($inputs['type']){
						case("get"):
							authorization::haveOrFail('get_list');
							$get = new get();
							$get->where('id', $inputs['forward']);
							if($get = $get->getOne()){
								$view->setDataForm("FWD: {$get->subject}", 'subject');
								$view->setDataForm($get->html, 'html');
							}
							break;
						case("sent"):
							authorization::haveOrFail('sent_list');
							$sent = new sent();
							db::join("userpanel_users", "userpanel_users.id=email_sent.receiver_user", "inner");
							if($types){
								$sent->where("userpanel_users.type", $types, 'in');
							}else{
								$sent->where("userpanel_users.id", authentication::getID());
							}
							$sent->where('email_sent.id', $inputs['forward']);
							if($sent = $sent->getOne("email_sent.*")){
								$view->setDataForm("FWD: {$sent->subject}", 'subject');
								$view->setDataForm($sent->html, 'html');
							}
							break;
					}
				}
			}
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function get_view($data){
		authorization::haveOrFail('get_view');
		if(!$email = get::byId($data['email'])){
			throw new NotFound();
		}
		$view = view::byName(views\get\view::class);
		$view->setEmail($email);
		
		$this->response->setStatus(true);
		if($email->html){
			$content = $email->html;
			$allows = "<html><head><body><p><a><b><strong><i><div><u><ul><li><ol><img><audio><video><span><section><aside><meta><form><button><input><h1><h2><h3><h4><h5><h6><style><small><table><tbody><thead><th><td><tr><option><select><fieldset>";
			$content = strip_tags($content, $allows);
			if(!http::getURIData('externalFiles')){
				$content = preg_replace('/src\=(?:\"([^\"]+)\"|\'([^\']+)\')/', 'src=""', $content);
				$content = preg_replace('/\@import[^\"|^\'|^\;]+/', '#', $content);
			}
			$view->setContent($content);
			$view->isHTML();
			$view->hasExternalFiles((bool)http::getURIData('externalFiles'));
		}else{
			$view->setContent($email->text);
			$view->isText();
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function sent_view($data){
		authorization::haveOrFail('sent_view');
		if(!$email = sent::byId($data['email'])){
			throw new NotFound();
		}
		$view = view::byName(views\sent\view::class);
		$view->setEmail($email);
		
		$this->response->setStatus(true);
		if($email->html){
			$view->setContent($email->html);
			$view->isHTML();
		}else{
			$view->setContent($email->text);
			$view->isText();
		}
		$this->response->setView($view);
		return $this->response;
	}
}
class sendException extends \Exception{}
