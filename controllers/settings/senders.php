<?php
namespace packages\email\controllers\settings;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\db\parenthesis;
use \packages\base\db\duplicateRecord;
use \packages\base\views\FormError;
use \packages\base\view\error;
use \packages\base\inputValidation;
use \packages\base\events;
use \packages\base\options;
use \packages\base\utility\safe;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \packages\email\view;
use \packages\email\authentication;
use \packages\email\controller;
use \packages\email\authorization;
use \packages\email\sender;
use \packages\email\sender\address;
use \packages\email\events\senders as sendersEvent;

use \packages\email\api;

class senders extends controller{
	protected $authentication = true;
	public function listsenders(){
		authorization::haveOrFail('settings_senders_list');
		$view = view::byName("\\packages\\email\\views\\settings\\senders\\listview");
		$senders = new sendersEvent();
		events::trigger($senders);
		$sender = new sender();
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
			'sender' => array(
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
				if(!in_array($inputs['status'], array(sender::active, sender::deactive))){
					throw new inputValidation("status");
				}
			}
			if(isset($inputs['sender']) and $inputs['sender']){
				if(!in_array($inputs['sender'], $senders->getSenderNames())){
					throw new inputValidation("sender");
				}
			}

			foreach(array('id', 'title', 'sender', 'status') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id','sender', 'status'))){
						$comparison = 'equals';
						if($item == 'sender'){
							$inputs[$item] = $senders->getByName($inputs[$item]);
						}
					}
					$sender->where($item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new parenthesis();
				foreach(array('title') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where("email_senders.".$item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$sender->where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$sender->orderBy('id', 'ASC');
		$sender->pageLimit = $this->items_per_page;
		$items = $sender->paginate($this->page);
		$view->setPaginate($this->page, $sender->totalCount, $this->items_per_page);
		$view->setDataList($items);
		$view->setSenders($senders);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		authorization::haveOrFail('settings_senders_add');
		$view = view::byName("\\packages\\email\\views\\settings\\senders\\add");
		$senders = new sendersEvent();
		events::trigger($senders);
		$view->setSenders($senders);
		if(http::is_post()){
			$inputsRules = array(
				'title' => array(
					'type' => 'string'
				),
				'sender' => array(
					'type' => 'string',
					'values' => $senders->getSenderNames()
				),
				'status' => array(
					'type' => 'number',
					'values' => array(sender::active, sender::deactive)
				),
				'addresses' => array()
			);
			$this->response->setStatus(true);
			try{
				$inputs = $this->checkinputs($inputsRules);
				$sender =  $senders->getByName($inputs['sender']);
				if($GRules = $sender->getInputs()){
					$GRules = $inputsRules = array_merge($inputsRules, $GRules);
					$ginputs = $this->checkinputs($GRules);
				}
				if(isset($inputs['addresses'])){
					if(is_array($inputs['addresses'])){
						foreach($inputs['addresses'] as $key => $data){
							if(isset($data['name']) and $data['name']){
								if(isset($data['address']) and safe::is_email($data['address'])){
									if(isset($data['status']) and in_array($data['status'], array(address::active, address::deactive))){
										if(address::byAddress($data['address'])){
											throw new duplicateRecord("address[{$key}][address]");
										}
									}else{
										throw new inputValidation("address[{$key}][status]");
									}
								}else{
									throw new inputValidation("address[{$key}][address]");
								}
							}else{
								throw new inputValidation("address[{$key}][name]");
							}
						}
					}else{
						throw new inputValidation("address");
					}
				}
				if($GRules = $sender->getInputs()){
					$sender->callController($ginputs);
				}
				$senderObj = new sender();
				$senderObj->title = $inputs['title'];
				$senderObj->handler = $sender->getHandler();
				$senderObj->status = $inputs['status'];
				foreach($sender->getInputs() as $input){
					if(isset($ginputs[$input['name']])){
						$senderObj->setParam($input['name'],$ginputs[$input['name']]);
					}
				}
				$senderObj->save();
				if(isset($inputs['addresses'])){
					foreach($inputs['addresses'] as $data){
						$address = new address();
						$address->sender = $senderObj->id;
						$address->name = $data['name'];
						$address->address = $data['address'];
						$address->status = $data['status'];
						$address->save();
						if(isset($data['primary']) and $data['primary']){
							options::save('packages.email.defaultAddress', $address->id);
						}
					}
				}
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('settings/email/senders/edit/'.$senderObj->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(duplicateRecord $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		authorization::haveOrFail('settings_senders_delete');
		if(!$sender = sender::byID($data['sender'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\senders\\delete");
		$view->setSender($sender);
		if(http::is_post()){
			$sender->delete();

			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/email/senders'));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		authorization::haveOrFail('settings_senders_edit');
		if(!$senderObj = sender::byID($data['sender'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\senders\\edit");
		$senders = new sendersEvent();
		events::trigger($senders);
		$view->setSenders($senders->get());
		$view->setSender($senderObj);
		if(http::is_post()){
			$inputsRules = array(
				'title' => array(
					'type' => 'string'
				),
				'sender' => array(
					'type' => 'string',
					'values' => $senders->getSenderNames()
				),
				'status' => array(
					'type' => 'number',
					'values' => array(sender::active, sender::deactive)
				),
				'addresses' => array()
			);
			$this->response->setStatus(true);
			try{
				$inputs = $this->checkinputs($inputsRules);
				$sender =  $senders->getByName($inputs['sender']);
				if($GRules = $sender->getInputs()){
					$GRules = $inputsRules = array_merge($inputsRules, $GRules);
					$ginputs = $this->checkinputs($GRules);
				}
				if(isset($inputs['addresses'])){
					if(is_array($inputs['addresses'])){
						foreach($inputs['addresses'] as $key => $data){
							if(isset($data['name']) and $data['name']){
								if(isset($data['address']) and safe::is_email($data['address'])){
									if(isset($data['status']) and in_array($data['status'], array(address::active, address::deactive))){
										if(address::where("sender", $senderObj->id, '!=')->byAddress($data['address'])){
											throw new duplicateRecord("address[{$key}][address]");
										}
									}else{
										throw new inputValidation("address[{$key}][status]");
									}
								}else{
									throw new inputValidation("address[{$key}][address]");
								}
							}else{
								throw new inputValidation("address[{$key}][name]");
							}
						}
					}else{
						throw new inputValidation("address");
					}
				}
				if($GRules = $sender->getInputs()){
					$sender->callController($ginputs);
				}
				$senderObj->title = $inputs['title'];
				$senderObj->handler = $sender->getHandler();
				$senderObj->status = $inputs['status'];
				foreach($sender->getInputs() as $input){
					if(isset($ginputs[$input['name']])){
						$senderObj->setParam($input['name'],$ginputs[$input['name']]);
					}
				}
				$senderObj->save();
				if(isset($inputs['addresses'])){
					foreach($inputs['addresses'] as $data){
						$addressObj = null;
						foreach($senderObj->addresses as $address){
							if($address->address == $data['address']){
								$addressObj = $address;
								break;
							}
						}
						if(!$addressObj){
							$addressObj = new address();
							$addressObj->sender = $senderObj->id;
						}
						$addressObj->name = $data['name'];
						$addressObj->address = $data['address'];
						$addressObj->status = $data['status'];
						$addressObj->save();
						if(isset($data['primary']) and $data['primary']){
							options::save('packages.email.defaultAddress', $address->id);
						}
					}
					foreach($senderObj->addresses as $address){
						$found = false;
						foreach($inputs['addresses'] as $data){
							if($address->address == $data['address']){
								$found = true;
								break;
							}
						}
						if(!$found){
							$address->delete();
						}
					}
				}
				$this->response->setStatus(true);
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(duplicateRecord $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
