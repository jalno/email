<?php
namespace packages\email\listeners;
use \packages\base\db;
use \packages\base\db\parenthesis;
use \packages\base\translator;

use \packages\userpanel;
use \packages\userpanel\date;
use \packages\userpanel\events\search as event;
use \packages\userpanel\search as saerchHandler;
use \packages\userpanel\search\link;

use \packages\email\authorization;
use \packages\email\authentication;
use \packages\email\get;
use \packages\email\sent;

class search{
	public function find(event $e){
		if(authorization::is_accessed('sent_list')){
			$this->sent($e->word);
		}
		if(authorization::is_accessed('get_list')){
			$this->get($e->word);
		}
	}
	public function get($word){
		$types = authorization::childrenTypes();
		$get_list_anonymous = authorization::is_accessed('get_list_anonymous');
		$parenthesis = new parenthesis();
		foreach(array('sender_number', 'receiver_number', 'text') as $item){
			$parenthesis->where("sms_get.".$item,$word, 'contains', 'OR');
		}
		db::where($parenthesis);
		if($get_list_anonymous){
			db::join("userpanel_users", "userpanel_users.id=sms_get.sender_user", "left");
			$parenthesis = new parenthesis();
			$parenthesis->where("userpanel_users.type",  $types, 'in');
			$parenthesis->where("sms_get.sender_user", null, 'is','or');
			db::where($parenthesis);
		}else{
			db::join("userpanel_users", "userpanel_users.id=sms_get.sender_user", "inner");
			if($types){
				db::where("userpanel_users.type", $types, 'in');
			}else{
				db::where("userpanel_users.id", authentication::getID());
			}
		}
		db::orderBy('sms_get.id', 'DESC');
		$items = db::get('sms_get', null, array("sms_get.*"));
		$gets = array();
		foreach($items  as $item){
			$gets[] = new get($item);
		}
		foreach($gets as $get){
			$result = new link();
			$result->setLink(userpanel\url('sms/get', array('id' => $get->id)));
			$result->setTitle(translator::trans("sms.get.bySenderNumber", array(
				'senderNumber' => $get->sender_number
			)));
			$result->setDescription(translator::trans("sms.get.description", array(
				'receive_at' => date::format("Y/m/d H:i:s", $get->receive_at),
				'text' => mb_substr($get->text, 0, 70)
			)));
			saerchHandler::addResult($result);
		}

	}
	public function sent($word){
		$types = authorization::childrenTypes();
		$sent_list_anonymous = authorization::is_accessed('sent_list_anonymous');
		$parenthesis = new parenthesis();
		foreach(array('sender_number', 'receiver_number', 'text') as $item){
			$parenthesis->where("sms_sent.".$item,$word, 'contains', 'OR');
		}
		db::where($parenthesis);
		if($sent_list_anonymous){
			db::join("userpanel_users", "userpanel_users.id=sms_sent.receiver_user", "left");
			$parenthesis = new parenthesis();
			$parenthesis->where("userpanel_users.type",  $types, 'in');
			$parenthesis->where("sms_sent.receiver_user", null, 'is','or');
			db::where($parenthesis);
		}else{
			db::join("userpanel_users", "userpanel_users.id=sms_sent.receiver_user", "inner");
			if($types){
				db::where("userpanel_users.type", $types, 'in');
			}else{
				db::where("userpanel_users.id", authentication::sentID());
			}
		}
		db::orderBy('sms_sent.id', 'DESC');
		$items = db::get('sms_sent', null, array("sms_sent.*"));
		$sents = array();
		foreach($items  as $item){
			$sents[] = new sent($item);
		}
		foreach($sents as $sent){
			$result = new link();
			$result->setLink(userpanel\url('sms/sent', array('id' => $sent->id)));
			$result->setTitle(translator::trans("sms.sent.byReceiverNumber", array(
				'receiverNumber' => $sent->receiver_number
			)));
			$result->setDescription(translator::trans("sms.sent.description", array(
				'send_at' => date::format("Y/m/d H:i:s", $sent->send_at),
				'text' => mb_substr($sent->text, 0, 70)
			)));
			saerchHandler::addResult($result);
		}

	}
}
