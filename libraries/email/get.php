<?php
namespace packages\email;
use \packages\base\db\dbObject;
use \packages\base\date;
use \packages\base\utility\safe;
use \packages\userpanel\user;
class get extends dbObject{
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
		'sender_user' => array('hasOne', 'packages\\userpanel\\user', 'sender_user'),
		'receiver' => array('hasOne', 'packages\\email\\receiver', 'receiver')
	);
	public function preLoad($data){
		if(!isset($data['receive_at'])){
			$data['receive_at'] = date::time();
		}
		if(!isset($data['sender_user'])){
			$user = new user();
			if($user = $user->where("email", $data['sender_address'])->getOne()){
				$data['sender_user'] = $user->id;
			}
		}
		if(!isset($data['status'])){
			$data['status'] = self::unread;
		}
		return $data;
	}
}
