<?php
namespace packages\email\Listeners\UserPanel\Users;

use packages\base\{View\Error};
use packages\email\{Authorization, Get, Sent};
use packages\userpanel\Events as UserpanelEvents;
use function packages\userpanel\url;

class BeforeDelete {
	public function check(UserpanelEvents\Users\BeforeDelete $event): void {
		$this->checkSentEmailsSender($event);
		$this->checkSentEmailsReceiver($event);
		$this->checkGetEmailsReceiver($event);
	}
	private function checkSentEmailsSender(UserpanelEvents\Users\BeforeDelete $event): void {
		$user = $event->getUser();
		$hasSentEmails = (new Sent)->where("sender_user", $user->id)->has();
		if (!$hasSentEmails) {
			return;
		}
		$message = t("error.packages.emails.error.emails.sent.sender_user.delete_user_warn.message");
		$error = new Error("packages.emails.error.emails.sent.sender_user.delete_user_warn");
		$error->setType(Error::WARNING);
		if (Authorization::is_accessed("sent_list")) {
			$message .= "<br> " . t("packages.emails.error.emails.sent.sender_user.delete_user_warn.view_emails") . " ";
			$error->setData(array(
				array(
					"txt" => '<i class="fa fa-search"></i> ' . t("packages.emails.error.emails.sent.sender_user.delete_user_warn.view_emails_btn"),
					"type" => "btn-warning",
					"link" => url("email/sent", array(
						"sender_user" => $user->id,
					)),
				),
			), "btns");
		} else {
			$message .= "<br> " . t("packages.emails.error.emails.sent.sender_user.delete_user_warn.view_emails.sent.tell_someone");
		}
		$error->setMessage($message);

		$event->addError($error);
	}
	private function checkSentEmailsReceiver(UserpanelEvents\Users\BeforeDelete $event): void {
		$user = $event->getUser();
		$hasSentEmails = (new Sent)->where("receiver_user", $user->id)->has();
		if (!$hasSentEmails) {
			return;
		}
		$message = t("error.packages.emails.error.emails.sent.receiver_user.delete_user_warn.message");
		$error = new Error("packages.emails.error.emails.sent.receiver_user.delete_user_warn");
		$error->setType(Error::WARNING);
		if (Authorization::is_accessed("sent_list")) {
			$message .= "<br> " . t("packages.emails.error.emails.sent.receiver_user.delete_user_warn.view_emails") . " ";
			$error->setData(array(
				array(
					"txt" => '<i class="fa fa-search"></i> ' . t("packages.emails.error.emails.sent.receiver_user.delete_user_warn.view_emails_btn"),
					"type" => "btn-warning",
					"link" => url("email/sent", array(
						"receiver_user" => $user->id,
					)),
				),
			), "btns");
		} else {
			$message .= "<br> " . t("packages.emails.error.emails.sent.receiver_user.delete_user_warn.view_emails.tell_someone");
		}
		$error->setMessage($message);

		$event->addError($error);
	}
	private function checkGetEmailsReceiver(UserpanelEvents\Users\BeforeDelete $event): void {
		$user = $event->getUser();
		$hasGetEmails = (new Get)->where("sender_user", $user->id)->has();
		if (!$hasGetEmails) {
			return;
		}
		$message = t("error.packages.emails.error.emails.get.sender_user.delete_user_warn.message");
		$error = new Error("packages.emails.error.emails.get.sender_user.delete_user_warn");
		$error->setType(Error::WARNING);
		if (Authorization::is_accessed("get_list")) {
			$message .= "<br> " . t("packages.emails.error.emails.get.sender_user.delete_user_warn.view_emails") . " ";
			$error->setData(array(
				array(
					"txt" => '<i class="fa fa-search"></i> ' . t("packages.emails.error.emails.get.sender_user.delete_user_warn.view_emails_btn"),
					"type" => "btn-warning",
					"link" => url("email/sent", array(
						"sender_user" => $user->id,
					)),
				),
			), "btns");
		} else {
			$message .= "<br> " . t("packages.emails.error.emails.get.sender_user.delete_user_warn.view_emails.tell_someone");
		}
		$error->setMessage($message);

		$event->addError($error);
	}
}
