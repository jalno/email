<?php
namespace packages\email;
use \packages\base\DB\DBObject;
use \packages\base\Date;
use \packages\base\Utility\Safe;
use \packages\userpanel\User;
class Get extends DBObject{
	const unread = 1;
	const read = 2;
	protected $dbTable = "email_get";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'serverid' => array('type' => 'text'),
        'receive_at' => array('type' => 'int', 'required' => true),
        'sender_name' => array('type' => 'text'),
        'sender_address' => array('type' => 'text', 'required' => true),
        'sender_user' => array('type' => 'int'),
        'receiver' => array('type' => 'int', 'required' => true),
        'receiver_name' => array('type' => 'text'),
        'receiver_address' => array('type' => 'text', 'required' => true),
		'subject' => array('type' => 'text'),
		'text' => array('type' => 'text'),
		'html' => array('type' => 'text'),
		'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'sender_user' => array('hasOne', User::class, 'sender_user'),
		'receiver' => array('hasOne', \packages\email\Receiver::class, 'receiver')
	);
	public function preLoad($data){
		if(!isset($data['receive_at'])){
			$data['receive_at'] = Date::time();
		}
		if(!isset($data['sender_user'])){
			$user = new User();
			if($user = $user->where("email", $data['sender_address'])->getOne()){
				$data['sender_user'] = $user->id;
			}
		}
		if(!isset($data['status'])){
			$data['status'] = self::unread;
		}
		return $data;
	}
	public function getContent():string{
		return $this->html ? $this->html : $this->text;
	}
}
