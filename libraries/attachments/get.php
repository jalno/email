<?php
namespace packages\email\get;
use \packages\base\db\dbObject;
class attachment extends dbObject{
	protected $dbTable = "email_get_attachments";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'mail' => array('type' => 'int', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'file' => array('type' => 'text', 'required' => true)
    );
	protected $relations = array(
		'mail' => array('hasOne', 'packages\\email\\get', 'mail')
	);
}
