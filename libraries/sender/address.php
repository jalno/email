<?php
namespace packages\email\sender;
use \packages\base\db\dbObject;
use \packages\base\options;
class address extends dbObject{
	const active = 1;
	const deactive = 2;
	protected $dbTable = "email_senders_addresses";
	protected $primaryKey = "id";
	protected $dbFields = array(
		'sender' => array('type' => 'int', 'required' => true),
		'name' => array('type' => 'text', 'required' => true),
		'address' => array('type' => 'text', 'required' => true),
        'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'sender' => array('hasOne', 'packages\\email\\sender', 'sender')
	);
	protected function byAddress($address){
		$this->where("address", $address);
		return $this->getOne();
	}
}
