<?php
namespace packages\email\Controllers\Settings;
use \packages\base;
use \packages\base\Frontend\Theme;
use \packages\base\NotFound;
use \packages\base\HTTP;
use \packages\base\DB;
use \packages\base\DB\Parenthesis;
use \packages\base\DB\DuplicateRecord;
use \packages\base\Views\FormError;
use \packages\base\View\Error;
use \packages\base\InputValidation;
use \packages\base\Events;
use \packages\base\Options;
use \packages\base\Utility\Safe;

use \packages\userpanel;
use \packages\userpanel\User;
use \packages\userpanel\Date;

use \packages\email\View;
use \packages\email\Authentication;
use \packages\email\Controller;
use \packages\email\Authorization;
use \packages\email\Sender;
use \packages\email\Sender\Address;
use \packages\email\Events\Senders as SendersEvent;

use \packages\email\API;

class Senders extends Controller{
	protected $authentication = true;
	public function listsenders(){
		Authorization::haveOrFail('settings_senders_list');
		$view = View::byName(\packages\email\Views\Settings\Senders\ListView::class);
		$senders = new SendersEvent();
		Events::trigger($senders);
		$sender = new Sender();
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
				if(!in_array($inputs['status'], array(Sender::active, Sender::deactive))){
					throw new InputValidation("status");
				}
			}
			if(isset($inputs['sender']) and $inputs['sender']){
				if(!in_array($inputs['sender'], $senders->getSenderNames())){
					throw new InputValidation("sender");
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
				$parenthesis = new Parenthesis();
				foreach(array('title') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where("email_senders.".$item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$sender->where($parenthesis);
			}
		}catch(InputValidation $error){
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
		Authorization::haveOrFail('settings_senders_add');
		$view = View::byName(\packages\email\Views\Settings\Senders\Add::class);
		$senders = new SendersEvent();
		Events::trigger($senders);
		$view->setSenders($senders);
		if(HTTP::is_post()){
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
					'values' => array(Sender::active, Sender::deactive)
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
								if(isset($data['address']) and Safe::is_email($data['address'])){
									if(isset($data['status']) and in_array($data['status'], array(Address::active, Address::deactive))){
										if ((new Address)->byAddress($data['address'])) {
											throw new DuplicateRecord("address[{$key}][address]");
										}
									}else{
										throw new InputValidation("address[{$key}][status]");
									}
								}else{
									throw new InputValidation("address[{$key}][address]");
								}
							}else{
								throw new InputValidation("address[{$key}][name]");
							}
						}
					}else{
						throw new InputValidation("address");
					}
				}
				if($GRules = $sender->getInputs()){
					$sender->callController($ginputs);
				}
				$senderObj = new Sender();
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
						$address = new Address();
						$address->sender = $senderObj->id;
						$address->name = $data['name'];
						$address->address = $data['address'];
						$address->status = $data['status'];
						$address->save();
						if(isset($data['primary']) and $data['primary']){
							Options::save('packages.email.defaultAddress', $address->id);
						}
					}
				}
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('settings/email/senders/edit/'.$senderObj->id));
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(DuplicateRecord $error){
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
		Authorization::haveOrFail('settings_senders_delete');
		if (!$sender = (new Sender)->byID($data['sender'])) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Senders\Delete::class);
		$view->setSender($sender);
		if(HTTP::is_post()){
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
		Authorization::haveOrFail('settings_senders_edit');
		if (!$senderObj = (new Sender)->byID($data['sender'])) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Senders\Edit::class);
		$senders = new SendersEvent();
		Events::trigger($senders);
		$view->setSenders($senders->get());
		$view->setSender($senderObj);
		if(HTTP::is_post()){
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
					'values' => array(Sender::active, Sender::deactive)
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
								if(isset($data['address']) and Safe::is_email($data['address'])){
									if(isset($data['status']) and in_array($data['status'], array(Address::active, Address::deactive))){
										if(Address::where("sender", $senderObj->id, '!=')->byAddress($data['address'])){
											throw new DuplicateRecord("address[{$key}][address]");
										}
									}else{
										throw new InputValidation("address[{$key}][status]");
									}
								}else{
									throw new InputValidation("address[{$key}][address]");
								}
							}else{
								throw new InputValidation("address[{$key}][name]");
							}
						}
					}else{
						throw new InputValidation("address");
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
							$addressObj = new Address();
							$addressObj->sender = $senderObj->id;
						}
						$addressObj->name = $data['name'];
						$addressObj->address = $data['address'];
						$addressObj->status = $data['status'];
						$addressObj->save();
						if(isset($data['primary']) and $data['primary']){
							Options::save('packages.email.defaultAddress', $address->id);
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
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(DuplicateRecord $error){
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
