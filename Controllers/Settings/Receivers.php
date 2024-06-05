<?php
namespace packages\email\Controllers\Settings;
use \packages\base;
use \packages\base\Frontend\Theme;
use \packages\base\NotFound;
use \packages\base\HTTP;
use \packages\base\DB;
use \packages\base\DB\Parenthesis;
use \packages\base\Views\FormError;
use \packages\base\View\Error;
use \packages\base\InputValidation;
use \packages\base\Options;
use \packages\base\Utility\Safe;

use \packages\userpanel;
use \packages\userpanel\User;

use \packages\email\View;
use \packages\email\Authentication;
use \packages\email\Controller;
use \packages\email\Authorization;
use \packages\email\Receiver;
use \packages\email\Imap\Exception as ImapException;

class Receivers extends Controller{
	protected $authentication = true;
	public function listreceivers(){
		Authorization::haveOrFail('settings_receivers_list');
		$view = View::byName(\packages\email\Views\Settings\Receivers\ListView::class);
		$receiver = new Receiver();
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
				if(!in_array($inputs['type'], array(Receiver::IMAP, Receiver::POP3, Receiver::NNTP))){
					throw new InputValidation("type");
				}
			}
			if(isset($inputs['encryption']) and $inputs['encryption'] != 0){
				if(!in_array($inputs['encryption'], array(Receiver::SSL, Receiver::TLS))){
					throw new InputValidation("encryption");
				}
			}
			if(isset($inputs['status']) and $inputs['status'] != 0){
				if(!in_array($inputs['status'], array(Receiver::active, Receiver::deactive))){
					throw new InputValidation("status");
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
				$parenthesis = new Parenthesis();
				foreach(array('title','hostname','username') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$receiver->where($parenthesis);
			}
		}catch(InputValidation $error){
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
		Authorization::haveOrFail('settings_receivers_add');
		$view = View::byName(\packages\email\Views\Settings\Receivers\Add::class);
		if(HTTP::is_post()){
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
					'values' => array(Receiver::IMAP, Receiver::POP3, Receiver::NNTP)
				),
				'encryption' => array(
					'values' => array(Receiver::SSL, Receiver::TLS),
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
					'values' => array(Receiver::active, Receiver::deactive)
				),
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);

				if($inputs['port'] < 1 or $inputs['port'] > 65535){
					throw new InputValidation("port");
				}

				$receiver = new Receiver();
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
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(ImapException $e){
				$error = new Error();
				$error->setCode('emails.receiver.connect');
				$error->setType(Error::FATAL);
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
		Authorization::haveOrFail('settings_receivers_delete');
		$receiver = (new Receiver)->byID($data['receiver']);
		if (!$receiver) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Receivers\Delete::class);
		$view->setReceiver($receiver);
		if(HTTP::is_post()){
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
		Authorization::haveOrFail('settings_receivers_edit');
		$receiver = (new Receiver)->byID($data['receiver']);
		if (!$receiver) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Receivers\Edit::class);
		$view->setReceiver($receiver);
		if(HTTP::is_post()){
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
					'values' => array(Receiver::IMAP, Receiver::POP3, Receiver::NNTP),
					'optional' => true
				),
				'encryption' => array(
					'values' => array(Receiver::SSL, Receiver::TLS),
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
					'values' => array(Receiver::active, Receiver::deactive),
					'optional' => true
				),
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(array_key_exists('port', $inputs)){
					if($inputs['port'] < 1 or $inputs['port'] > 65535){
						throw new InputValidation("port");
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
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(ImapException $e){
				$error = new Error();
				$error->setCode('emails.receiver.connect');
				$error->setType(Error::FATAL);
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
