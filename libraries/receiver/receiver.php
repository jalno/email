<?php
namespace packages\email;
use \packages\base\events;
use \packages\base\db\dbObject;
use \packages\email\get;
use \packages\email\imap;
use \packages\email\events as emailEvents;
class receiver extends dbObject{
	const active = 1;
	const deactive = 2;
	const IMAP = 1;
	const POP3 = 2;
	const NNTP = 3;
	const SSL = 1;
	const TLS = 2;
	protected $dbTable = "email_receivers";
	protected $primaryKey = "id";
	protected $driver;
	protected $dbFields = array(
		'title' => array('type' => 'text', 'required' => true),
		'type' => array('type' => 'int', 'required' => true),
		'hostname' => array('type' => 'text', 'required' => true),
		'port' => array('type' => 'int', 'required' => true),
		'username' => array('type' => 'text', 'required' => true),
		'password' => array('type' => 'text', 'required' => true),
		//'authentication' => array('type' => 'int', 'required' => true),
		'encryption' => array('type' => 'int'),
        'status' => array('type' => 'int', 'required' => true)
    );
	protected function preLoad($data){
		if(!$data['encryption']){
			$data['encryption'] = 0;
		}
		return $data;
	}
	public function connect(): imap\MailBox {
		$type = '';
		switch ($this->type) {
			case(self::IMAP):$type = 'imap';break;
			case(self::POP3):$type = 'pop3';break;
			case(self::NNTP):$type = 'NNTP';break;
		}

		$encryption = '';
		switch ($this->encryption) {
			case(self::SSL):$encryption = 'ssl';break;
			case(self::TLS):$encryption = 'tls';break;
		}

		$path = '{' . $this->hostname . ':' . $this->port . '/' . $type;
		if ($encryption) {
			$path .= '/' . $encryption;
		}
		$path .= "}INBOX";

		return $this->driver = new imap\Mailbox($path, $this->username, $this->password);
	}
	public function check(){
		if(!$this->driver){
			$this->connect();
		}
		return $this->driver->searchMailbox("UNSEEN");
	}
	public function getEmails($messages){
		foreach($messages as $message){
			$this->getEmail($message);
		}
	}
	public function getEmail($message){
		if($email = $this->driver->getMail($message)){
			$toEmails = array_keys($email->to);
			$toNames = array_values($email->to);
			$get = new get();
			$get->serverid = $message;
			$get->receive_at = $email->time;
			$get->sender_name = $email->fromName;
			$get->sender_address = $email->fromAddress;
			$get->receiver = $this->id;
			$get->receiver_name = $toNames[0];
			$get->receiver_address = $toEmails[0];
			$get->subject = $email->subject;
			$get->text = $email->textPlain;
			$get->html = $email->textHtml;
			$get->save();
			$this->driver->markMailAsRead($message);
			foreach($email->getAttachments() as $attachment){

			}
			events::trigger(new emailEvents\receive($get));
		}
	}
}
