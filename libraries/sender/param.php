<?php
namespace packages\email\sender;
use \packages\base\db\dbObject;
class param extends dbObject{
	protected $dbTable = "email_senders_params";
	protected $primaryKey = "id";
	protected $dbFields = array(
		'sender' => array('type' => 'int', 'required' => true),
		'name' => array('type' => 'text', 'required' => true),
        'value' => array('type' => 'text')
    );
}
