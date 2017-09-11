<?php
namespace packages\email\views\sent;
use \packages\email\sent as email;
use \packages\email\Html2Text;
class view extends \packages\email\view{
	public function setEmail(email $email){
		$this->setData($email, "email");
	}
	protected function getEmail():email{
		return $this->getData("email");
	}
	public function setContent(string $content){
		$this->setData($content, 'content');
	}
	protected function getContent():string{
		return $this->getData('content');
	}
	public function isHTML(){
		$this->setData('html', 'content-type');
	}
	public function isText(){
		$this->setData('text', 'content-type');
	}
	protected function getContentType():string{
		return $this->getData('content-type');
	}
	protected function getHTML():string{
		$content = $this->getContent();
		if($this->getContentType() == 'text'){
			$content = '<pre>'.htmlentities($content).'</pre>';
		}
		return $content;
	}
	protected function getText():string{
		$content = $this->getContent();
		if(!$content = $this->getEmail()->text){
			$content = Html2Text::convert($content, true);
		}
		return $content;
	}
	public function export(){
		$email = $this->getEmail();
		return array(
			'data' => array(
				'send_at' => $email->send_at,
				'sender_address' => $email->sender_address->toArray(true),
				'sender_user' => $email->data['sender_user'],
				'receiver_name' => $email->receiver_name,
				'receiver_address' => $email->receiver_address,
				'receiver_user' => $email->data['receiver_user'],
				'subject' => $email->subject,
				'text' => $this->getText(),
				'html' => $this->getHTML(),
				'status' => $email->status
			)
		);
	}
}
