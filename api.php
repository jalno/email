<?php
namespace packages\email;
use \packages\base\options;
use \packages\base\events;
use \packages\base\translator;
use \packages\base\packages;
use \packages\base\IO;
use \packages\base\date;
use \packages\userpanel\user;
use \packages\email\Html2Text;
use \packages\email\sent;
use \packages\email\template;
use \packages\email\sender;
use \packages\email\sender\address;
use \packages\email\events as emailEvents;

class api{
	private $subject;
	private $text;
	private $html;
	private $attachments = array();
	private $receiver_name;
	private $receiver_address;
	private $receiver_user;
	private $sender_address;
	private $sender_user;
	private $time;
	public function template($name,$parameters = array(),$lang = null){
		if($lang === null){
			$lang = translator::getShortCodeLang();
		}
		if(!$lang){
			throw new unkownLanguage();
		}
		$template = new template();
		$template->where('name', $name);
		$template->where('lang', $lang);
		$template->where('status', template::active);
		if($template = $template->getOne()){
			$render = $template->render($parameters);
			$this->text = $render['text'];
			$this->html = $render['html'];
			$this->subject = $render['subject'];
		}else{
			$this->text = '';
		}
		return $this;
	}
	public function to($address,$name = null){
		$this->receiver_address = $address;
		$this->name = $name;
		if($this->receiver_user === null){
			$user = new user();
			$user->where("email", $address);
			if($user = $user->getOne()){
				$this->toUser($user);
			}
		}
		return $this;
	}
	public function toUser(user $receiver_user){
		$this->receiver_user = $receiver_user;
		if(!$this->receiver_address){
			$this->receiver_address = $receiver_user->email;
		}
		if(!$this->receiver_name){
			$this->receiver_name = $receiver_user->getFullName();
		}
		return $this;
	}
	public function fromUser(user $sender_user){
		$this->sender_user  = $sender_user;
		return $this;
	}
	public function fromAddress(address $address){
		if($address->status == address::active and $address->sender->status == sender::active){
			$this->sender_address = $address;
		}else{
			throw new deactivedAdressException;
		}
		return $this;
	}
	public function fromDefaultAddress(){
		if($defaultAddress = options::get('packages.email.defaultAddress')){
			if($address = address::byID($defaultAddress)){
				$this->fromAddress($address);
			}else{
				throw new defaultAddressException();
			}
		}else{
			throw new defaultAddressException();
		}
	}
	public function addAttachment($file,$name = null){
		if(!is_file($file)){
			throw new attachmentException($file);
		}
		if(!$name){
			$name = basename($file);
		}
		$storage = packages::package('email')->getFilePath('storage/private/attachments/');
		if(!IO\is_dir($storage)){
			IO\mkdir($storage, true);
		}
		$real_storage = IO\realpath($storage);
		$real_file = IO\realpath($file);
		if(substr($real_file, 0, strlen($real_storage)) != $real_storage){
			$new = $storage.IO\md5($file);
			if(IO\copy($file, $new)){
				$file = $new;
			}
		}
		
		$this->attachments[] = array(
			'file' => $file,
			'name' => $name,
			'size' => IO\filesize($file)
		);
		return $this;
	}
	public function text($text){
		$this->text = $text;
		return $this;
	}
	public function html($html){
		$this->html = $html;
		if(!$this->text){
			$this->text = Html2Text::convert($html, true);
		}
		return $this;
	}
	public function subject($subject){
		$this->subject = $subject;
	}
	public function now(){
		$this->time = date::time();
		return $this;
	}
	public function at($time){
		$this->time = $time;
		return $this;
	}
	public function send(){
		$email = new sent();
		$email->send_at = $this->time;
		if(!$this->sender_address){
			$this->fromDefaultAddress();
		}

		$email->sender_address = $this->sender_address->id;
		if($this->sender_user){
			$email->sender_user = $this->sender_user->id;
		}
		$email->receiver_name = $this->receiver_name;
		$email->receiver_address = $this->receiver_address;
		if($this->receiver_user){
			$email->receiver_user = $this->receiver_user->id;
		}
		$email->subject = $this->subject;
		$email->text = $this->text;
		$email->html = $this->html;
		if($email->send_at >= date::time()){
			$email->status = sent::queued;
		}else{
			$email->status = sent::sending;
		}
		$email->save();
		foreach($this->attachments as $attachment){
			$attach = new sent\attachment();
			$attach->mail = $email->id;
			$attach->size = $attachment['size'];
			$attach->name = $attachment['name'];
			$attach->file = $attachment['file'];
			$attach->save();
		}
		if($email->send_at >= date::time()){
			$email->send();
		}
		events::trigger(new emailEvents\send($email));
		return $email->status;
	}
}
class unkownLanguage extends \Exception{

}
class deactivedAdressException extends \Exception{}
class defaultAddressException extends \Exception{}
class attachmentException extends \Exception{}
