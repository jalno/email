<?php
namespace packages\email\controllers\settings;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\db\parenthesis;
use \packages\base\views\FormError;
use \packages\base\view\error;
use \packages\base\inputValidation;
use \packages\base\options;
use \packages\base\utility\safe;

use \packages\userpanel;
use \packages\userpanel\user;

use \packages\email\view;
use \packages\email\authentication;
use \packages\email\controller;
use \packages\email\authorization;
use \packages\email\receiver;
use \packages\email\imap\Exception as imapException;

class receivers extends controller{
	protected $authentication = true;
	public function listreceivers(){
		authorization::haveOrFail('settings_receivers_list');
		$view = view::byName("\\packages\\email\\views\\settings\\receivers\\listview");
		$receiver = new receiver();
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'title' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'hostname' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'port' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'username' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'type' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'encryption' => array(
				'type' => 'number',
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
			if(isset($inputs['type']) and $inputs['type'] != 0){
				if(!in_array($inputs['type'], array(receiver::IMAP, receiver::POP3, receiver::NNTP))){
					throw new inputValidation("type");
				}
			}
			if(isset($inputs['encryption']) and $inputs['encryption'] != 0){
				if(!in_array($inputs['encryption'], array(receiver::SSL, receiver::TLS))){
					throw new inputValidation("encryption");
				}
			}
			if(isset($inputs['status']) and $inputs['status'] != 0){
				if(!in_array($inputs['status'], array(receiver::active, receiver::deactive))){
					throw new inputValidation("status");
				}
			}
			if(isset($inputs['receiver']) and $inputs['receiver']){
				if(!in_array($inputs['receiver'], $receivers->getReceiverNames())){
					throw new inputValidation("receiver");
				}
			}

			foreach(array('id', 'title', 'hostname', 'port','username','type','encryption','status') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id', 'port', 'type', 'encryption', 'status'))){
						$comparison = 'equals';
					}
					$receiver->where($item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new parenthesis();
				foreach(array('title','hostname','username') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$receiver->where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$receiver->orderBy('id', 'ASC');
		$receiver->pageLimit = $this->items_per_page;
		$items = $receiver->paginate($this->page);
		$view->setPaginate($this->page, $receiver->totalCount, $this->items_per_page);
		$view->setDataList($items);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		authorization::haveOrFail('settings_receivers_add');
		$view = view::byName("\\packages\\email\\views\\settings\\receivers\\add");
		if(http::is_post()){
			$inputsRules = array(
				'title' => array(
					'type' => 'string'
				),
				'hostname' => array(
					'regex' => '/^([a-z0-9\\-]+\\.)+[a-z]{2,12}$/i',
				),
				'port' => array(
					'type' => 'number'
				),
				'type' => array(
					'values' => array(receiver::IMAP, receiver::POP3, receiver::NNTP)
				),
				'encryption' => array(
					'values' => array(receiver::SSL, receiver::TLS),
					'empty' => true
				),
				'username' => array(
					'type' => 'string'
				),
				'password' => array(
					'type' => 'string'
				),
				'status' => array(
					'type' => 'number',
					'values' => array(receiver::active, receiver::deactive)
				),
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);

				if($inputs['port'] < 1 or $inputs['port'] > 65535){
					throw new inputValidation("port");
				}

				$receiver = new receiver();
				$receiver->title = $inputs['title'];
				$receiver->type = $inputs['type'];
				$receiver->hostname = $inputs['hostname'];
				$receiver->port = $inputs['port'];
				$receiver->type = $inputs['type'];
				$receiver->encryption = $inputs['encryption'];
				$receiver->username = $inputs['username'];
				$receiver->password = $inputs['password'];
				$receiver->status = $inputs['status'];
				$receiver->check();
				$receiver->save();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('settings/email/receivers/edit/'.$receiver->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(imapException $e){
				$error = new error();
				$error->setCode('emails.receiver.connect');
				$error->setType(error::FATAL);
				$view->addError($error);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		authorization::haveOrFail('settings_receivers_delete');
		if(!$receiver = receiver::byID($data['receiver'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\receivers\\delete");
		$view->setReceiver($receiver);
		if(http::is_post()){
			$receiver->delete();

			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/email/receivers'));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		authorization::haveOrFail('settings_receivers_edit');
		if(!$receiver = receiver::byID($data['receiver'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\receivers\\edit");
		$view->setReceiver($receiver);
		if(http::is_post()){
			$inputsRules = array(
				'title' => array(
					'type' => 'string',
					'optional' => true
				),
				'hostname' => array(
					'regex' => '/^([a-z0-9\\-]+\\.)+[a-z]{2,12}$/i',
					'optional' => true
				),
				'port' => array(
					'type' => 'number',
					'optional' => true
				),
				'type' => array(
					'values' => array(receiver::IMAP, receiver::POP3, receiver::NNTP),
					'optional' => true
				),
				'encryption' => array(
					'values' => array(receiver::SSL, receiver::TLS),
					'empty' => true,
					'optional' => true
				),
				'username' => array(
					'type' => 'string',
					'optional' => true
				),
				'password' => array(
					'type' => 'string',
					'optional' => true
				),
				'status' => array(
					'type' => 'number',
					'values' => array(receiver::active, receiver::deactive),
					'optional' => true
				),
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(array_key_exists('port', $inputs)){
					if($inputs['port'] < 1 or $inputs['port'] > 65535){
						throw new inputValidation("port");
					}
				}
				foreach(array(
					'title',
					'type',
					'hostname',
					'port',
					'type',
					'encryption',
					'username',
					'password',
					'status0'
				) as $input){
					if(array_key_exists($input, $inputs)){
						$receiver->$input = $inputs[$input];
					}
				}
				$receiver->check();
				$receiver->save();
				$this->response->setStatus(true);
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(imapException $e){
				$error = new error();
				$error->setCode('emails.receiver.connect');
				$error->setType(error::FATAL);
				$view->addError($error);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
