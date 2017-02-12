<?php
namespace packages\email;
use \packages\base\db\dbObject;
class sent extends dbObject{
	const queued = 1;
	const sending = 2;
	const sent = 3;
	const failed = 4;
	protected $tmparams = array();
	protected $dbTable = "email_sent";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'send_at' => array('type' => 'int', 'required' => true),
        'sender_address' => array('type' => 'text', 'required' => true),
        'sender_user' => array('type' => 'int'),
        'receiver_name' => array('type' => 'text'),
        'receiver_address' => array('type' => 'text', 'required' => true),
        'receiver_user' => array('type' => 'int'),
		'subject' => array('type' => 'text', 'required' => true),
		'text' => array('type' => 'text', 'required' => true),
		'html' => array('type' => 'text', 'required' => true),
		'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'sender_address' => array('hasOne', 'packages\\email\\sender\\address', 'sender_address'),
		'sender_user' => array('hasOne', 'packages\\userpanel\\user', 'sender_user'),
		'receiver_user' => array('hasOne', 'packages\\userpanel\\user', 'receiver_user'),
		'attachments' => array('hasMany', 'packages\\email\\sent\\attachment', 'mail')
	);
	public function preLoad($data){
		if(!isset($data['send_at'])){
			$data['send_at'] = time();
		}
		return $data;
	}
	public function send(){
		$this->status = self::sending;
		$this->save();
		$status = $this->sender_address->sender->send($this);
		if(in_array($status,array(self::sent, self::failed))){
			$this->status = $status;
		}else{
			$this->status = self::failed;
		}
		$this->save();
		return $this->status == self::sent;
	}
}
