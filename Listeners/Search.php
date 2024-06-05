<?php

namespace packages\email\Listeners;

use packages\base\DB;
use packages\base\DB\Parenthesis;
use packages\base\Translator;
use packages\email\Authorization;
use packages\email\Get;
use packages\email\Sent;
use packages\userpanel;
use packages\userpanel\Authentication;
use packages\userpanel\Date;
use packages\userpanel\Events\Search as Event;
use packages\userpanel\Search as SearchHandler;
use packages\userpanel\Search\Link;

class Search
{
    public function find(Event $e)
    {
        if (Authorization::is_accessed('sent_list')) {
            $this->sent($e->word);
        }
        if (Authorization::is_accessed('get_list')) {
            $this->get($e->word);
        }
    }

    public function get($word)
    {
        $types = Authorization::childrenTypes();
        $get_list_anonymous = Authorization::is_accessed('get_list_anonymous');
        $parenthesis = new Parenthesis();
        foreach (['sender_address', 'receiver_address', 'text'] as $item) {
            $parenthesis->where("email_get.{$item}", $word, 'contains', 'OR');
        }
        DB::where($parenthesis);
        if ($get_list_anonymous) {
            DB::join('userpanel_users', 'userpanel_users.id=email_get.sender_user', 'left');
            $parenthesis = new Parenthesis();
            $parenthesis->where('userpanel_users.type', $types, 'in');
            $parenthesis->where('email_get.sender_user', null, 'is', 'or');
            DB::where($parenthesis);
        } else {
            DB::join('userpanel_users', 'userpanel_users.id=sender_user.sender_user', 'inner');
            if ($types) {
                DB::where('userpanel_users.type', $types, 'in');
            } else {
                DB::where('userpanel_users.id', Authentication::getID());
            }
        }
        DB::orderBy('email_get.id', 'DESC');
        $items = DB::get('email_get', null, ['email_get.*']);
        $gets = [];
        foreach ($items as $item) {
            $gets[] = new Get($item);
        }
        foreach ($gets as $get) {
            $result = new Link();
            $result->setLink(userpanel\url('email/get', ['id' => $get->id]));
            $result->setTitle(Translator::trans('email.get.bySenderAddress', [
                'senderAddress' => $get->sender_address,
            ]));
            $result->setDescription(Translator::trans('email.get.description', [
                'receive_at' => Date::format('Y/m/d H:i:s', $get->receive_at),
                'text' => mb_substr($get->text, 0, 70),
            ]));
            SearchHandler::addResult($result);
        }
    }

    public function sent($word)
    {
        $types = Authorization::childrenTypes();
        $sent_list_anonymous = Authorization::is_accessed('sent_list_anonymous');
        $parenthesis = new Parenthesis();
        foreach (['sender_address', 'receiver_address', 'text'] as $item) {
            $parenthesis->where("email_sent.{$item}", $word, 'contains', 'OR');
        }
        DB::where($parenthesis);
        if ($sent_list_anonymous) {
            DB::join('userpanel_users', 'userpanel_users.id=email_sent.receiver_user', 'left');
            $parenthesis = new Parenthesis();
            $parenthesis->where('userpanel_users.type', $types, 'in');
            $parenthesis->where('email_sent.receiver_user', null, 'is', 'or');
            DB::where($parenthesis);
        } else {
            DB::join('userpanel_users', 'userpanel_users.id=email_sent.receiver_user', 'inner');
            if ($types) {
                DB::where('userpanel_users.type', $types, 'in');
            } else {
                DB::where('userpanel_users.id', Authentication::getID());
            }
        }
        DB::orderBy('email_sent.id', 'DESC');
        $items = DB::get('email_sent', null, ['email_sent.*']);
        $sents = [];
        foreach ($items as $item) {
            $sents[] = new Sent($item);
        }
        foreach ($sents as $sent) {
            $result = new Link();
            $result->setLink(userpanel\url('email/sent', ['id' => $sent->id]));
            $result->setTitle(Translator::trans('email.sent.byReceiverAddress', [
                'receiverAddress' => $sent->receiver_address,
            ]));
            $result->setDescription(Translator::trans('email.sent.description', [
                'send_at' => Date::format('Y/m/d H:i:s', $sent->send_at),
                'text' => mb_substr($sent->text, 0, 70),
            ]));
            SearchHandler::addResult($result);
        }
    }
}
