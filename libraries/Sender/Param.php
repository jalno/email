<?php
namespace packages\email\Sender;
use \packages\base\DB\DBObject;
class Param extends DBObject{
	protected $dbTable = "email_senders_params";
	protected $primaryKey = "id";
	protected $dbFields = array(
		'sender' => array('type' => 'int', 'required' => true),
		'name' => array('type' => 'text', 'required' => true),
        'value' => array('type' => 'text')
    );
}
