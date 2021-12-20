<?php
namespace packages\email\listeners;

use packages\base\{db, db\Parenthesis, Translator};
use packages\userpanel;
use packages\userpanel\{
	Authentication, Date, events\Search as Event, Search as SearchHandler, Search\Link
};
use packages\email\{Authorization, Get, Sent};

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
		foreach(array('sender_address', 'receiver_address', 'text') as $item){
			$parenthesis->where("email_get.{$item}", $word, 'contains', 'OR');
		}
		db::where($parenthesis);
		if($get_list_anonymous){
			db::join("userpanel_users", "userpanel_users.id=email_get.sender_user", "left");
			$parenthesis = new parenthesis();
			$parenthesis->where("userpanel_users.type",  $types, 'in');
			$parenthesis->where("email_get.sender_user", null, 'is','or');
			db::where($parenthesis);
		}else{
			db::join("userpanel_users", "userpanel_users.id=sender_user.sender_user", "inner");
			if($types){
				db::where("userpanel_users.type", $types, 'in');
			}else{
				db::where("userpanel_users.id", authentication::getID());
			}
		}
		db::orderBy('email_get.id', 'DESC');
		$items = db::get('email_get', null, array("email_get.*"));
		$gets = array();
		foreach($items  as $item){
			$gets[] = new get($item);
		}
		foreach($gets as $get){
			$result = new link();
			$result->setLink(userpanel\url('email/get', array('id' => $get->id)));
			$result->setTitle(translator::trans("email.get.bySenderAddress", array(
				'senderAddress' => $get->sender_address
			)));
			$result->setDescription(translator::trans("email.get.description", array(
				'receive_at' => date::format("Y/m/d H:i:s", $get->receive_at),
				'text' => mb_substr($get->text, 0, 70)
			)));
			SearchHandler::addResult($result);
		}

	}
	public function sent($word){
		$types = authorization::childrenTypes();
		$sent_list_anonymous = authorization::is_accessed('sent_list_anonymous');
		$parenthesis = new parenthesis();
		foreach(array('sender_address', 'receiver_address', 'text') as $item){
			$parenthesis->where("email_sent.{$item}", $word, 'contains', 'OR');
		}
		db::where($parenthesis);
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
		db::orderBy('email_sent.id', 'DESC');
		$items = db::get('email_sent', null, array("email_sent.*"));
		$sents = array();
		foreach($items  as $item){
			$sents[] = new sent($item);
		}
		foreach($sents as $sent){
			$result = new link();
			$result->setLink(userpanel\url('email/sent', array('id' => $sent->id)));
			$result->setTitle(translator::trans("email.sent.byReceiverAddress", array(
				'receiverAddress' => $sent->receiver_address
			)));
			$result->setDescription(translator::trans("email.sent.description", array(
				'send_at' => date::format("Y/m/d H:i:s", $sent->send_at),
				'text' => mb_substr($sent->text, 0, 70)
			)));
			SearchHandler::addResult($result);
		}

	}
}
