<?php

namespace packages\email\controllers;

use packages\base\db;
use packages\base\db\Parenthesis;
use packages\base\http;
use packages\base\NotFound;
use packages\base\utility\Safe;
use packages\base\view\Error;
use packages\base\views\FormError;
use packages\email\API;
use packages\email\Authentication;
use packages\email\Authorization;
use packages\email\Controller;
use packages\email\Get;
use packages\email\Sender;
use packages\email\sender\Address;
use packages\email\Sent;
use packages\email\View;
use packages\email\views;
use packages\userpanel;
use packages\userpanel\User;

class email extends Controller
{
    protected $authentication = true;

    public function sent()
    {
        Authorization::haveOrFail('sent_list');
        $view = View::byName(views\sent\listview::class);
        $this->response->setView($view);

        $inputs = $this->checkinputs([
            'id' => [
                'type' => 'number',
                'optional' => true,
            ],
            'sender_user' => [
                'type' => User::class,
                'optional' => true,
            ],
            'sender_number' => [
                'type' => 'number',
                'optional' => true,
            ],
            'receiver_user' => [
                'type' => User::class,
                'optional' => true,
            ],
            'receiver_number' => [
                'type' => 'number',
                'optional' => true,
            ],
            'text' => [
                'type' => 'string',
                'optional' => true,
            ],
            'status' => [
                'type' => 'number',
                'optional' => true,
                'values' => [Sent::queued, Sent::sending, Sent::sent, Sent::failed],
            ],
            'word' => [
                'type' => 'string',
                'optional' => true,
            ],
            'comparison' => [
                'values' => ['equals', 'startswith', 'contains'],
                'default' => 'contains',
                'optional' => true,
            ],
        ]);

        if (empty(Authorization::childrenTypes())) {
            foreach (['sender_user', 'sender_number', 'receiver_user', 'receiver_number'] as $item) {
                unset($inputs[$item]);
            }
        }

        $query = $this->getSentQuery();

        $query->orderBy('email_sent.id', 'DESC');
        foreach (['id', 'sender_user', 'receiver_user', 'sender_number', 'receiver_number', 'text', 'status'] as $item) {
            if (isset($inputs[$item])) {
                $comparison = $inputs['comparison'];
                if (in_array($item, ['id', 'status', 'sender_user', 'receiver_user'])) {
                    $comparison = 'equals';
                }
                $query->where('email_sent.'.$item, $inputs[$item], $comparison);
            }
        }
        if (isset($inputs['word'])) {
            $parenthesis = new Parenthesis();
            foreach (['sender_number', 'receiver_number', 'text'] as $item) {
                if (!isset($inputs[$item])) {
                    $parenthesis->where('email_sent.'.$item, $inputs['word'], $inputs['comparison'], 'OR');
                }
            }
            $query->where($parenthesis);
        }

        $query->pageLimit = $this->items_per_page;
        $items = $query->paginate($this->page, ['email_sent.*']);
        $view->setPaginate($this->page, $query->totalCount, $this->items_per_page);
        $view->setDataList($items);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function get($name)
    {
        Authorization::haveOrFail('get_list');
        $view = View::byName(views\get\listview::class);
        $this->response->setView($view);

        $inputs = $this->checkinputs([
            'id' => [
                'type' => 'number',
                'optional' => true,
            ],
            'sender_user' => [
                'type' => User::class,
                'optional' => true,
            ],
            'sender_number' => [
                'type' => 'number',
                'optional' => true,
            ],
            'receiver_number' => [
                'type' => 'number',
                'optional' => true,
            ],
            'text' => [
                'type' => 'string',
                'optional' => true,
            ],
            'status' => [
                'type' => 'number',
                'optional' => true,
                'values' => [Get::unread, Get::read],
            ],
            'word' => [
                'type' => 'string',
                'optional' => true,
            ],
            'comparison' => [
                'values' => ['equals', 'startswith', 'contains'],
                'default' => 'contains',
                'optional' => true,
            ],
        ]);

        $query = $this->getGetQuery();
        $query->orderBy('email_get.id', ' DESC');

        foreach (['id', 'sender_user', 'sender_number', 'receiver_number', 'text', 'status'] as $item) {
            if (isset($inputs[$item])) {
                $comparison = $inputs['comparison'];
                $value = $inputs[$item];
                if (in_array($item, ['id', 'status', 'sender_user'])) {
                    $comparison = 'equals';
                }
                if ('sender_user' === $item) {
                    $value = $value->id;
                }
                $query->where('email_get.'.$item, $value, $comparison);
            }
        }
        if (isset($inputs['word']) and $inputs['word']) {
            $parenthesis = new Parenthesis();
            foreach (['sender_number', 'receiver_number', 'text'] as $item) {
                if (!isset($inputs[$item]) or !$inputs[$item]) {
                    $parenthesis->where('email_get.'.$item, $inputs['word'], $inputs['comparison'], 'OR');
                }
            }
            $query->where($parenthesis);
        }

        $query->pageLimit = $this->items_per_page;
        $items = $query->paginate($this->page, ['email_get.*']);
        $view->setPaginate($this->page, $query->totalCount, $this->items_per_page);
        $view->setDataList($items);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function send()
    {
        $view = view::byName(views\Send::class);
        Authorization::haveOrFail('send');
        db::join('email_senders', 'email_senders_addresses.sender=email_senders.id', 'inner');
        db::where('email_senders.status', Sender::active);
        db::where('email_senders_addresses.status', Address::active);
        $addressesData = db::get('email_senders_addresses', null, 'email_senders_addresses.*');
        $addresses = [];
        foreach ($addressesData as $data) {
            $addresses[] = new Address($data);
        }

        $view->setAddresses($addresses);
        if (http::is_post()) {
            $this->response->setStatus(false);
            $inputsRules = [
                'to' => [],
                'from' => [
                    'type' => 'number',
                    'optional' => true,
                ],
                'subject' => [
                    'type' => 'string',
                ],
                'html' => [],
                'attachments' => [
                    'type' => 'file',
                    'optional' => true,
                    'empty' => true,
                ],
            ];
            try {
                $inputs = $this->checkinputs($inputsRules);

                if (array_key_exists('from', $inputs)) {
                    if (!$inputs['from'] = Address::byId($inputs['from'])) {
                        throw new InputValidationException('from');
                    }
                    if (Address::active != $inputs['from']->status or address::active != $inputs['from']->sender->status) {
                        throw new InputValidationException('from');
                    }
                }
                $inputs['to'] = explode(',', $inputs['to']);
                foreach ($inputs['to'] as $key => $to) {
                    $name = null;
                    $email = '';
                    $to = trim($to);
                    if (false !== strpos($to, '<')) {
                        if (preg_match('/(.+)\\s*\\<\\s*(.+)\\s*\\>/', $to, $matches)) {
                            $matches[1] = Safe::string($matches[1]);
                            $matches[2] = Safe::string($matches[2]);
                            if (!$matches[1]) {
                                throw new InputValidationException('to');
                            }
                            if (!Safe::is_email($matches[2])) {
                                throw new InputValidationException('to');
                            }
                            $name = $matches[1];
                            $email = $matches[2];
                        } else {
                            throw new InputValidationException('to');
                        }
                    } elseif (Safe::is_email($to)) {
                        $email = $to;
                    } else {
                        throw new InputValidationException('to');
                    }
                    $inputs['to'][$key] = [
                        'name' => $name,
                        'email' => $email,
                    ];
                }
                if (isset($formdata['attachments'])) {
                    foreach ($formdata['attachments'] as $key => $attachment) {
                        if (0 == $attachment['error']) {
                        } elseif (isset($attachment['error']) and 4 != $attachment['error']) {
                            throw new InputValidationException("attachments[{$key}]");
                        }
                    }
                }
                $sendone = false;
                foreach ($inputs['to'] as $receiver) {
                    $email = new API();
                    $email->to($receiver['email'], $receiver['name']);
                    $email->fromUser(Authentication::getUser());
                    $email->subject($inputs['subject']);
                    $email->html($inputs['html']);
                    if (array_key_exists('from', $inputs)) {
                        $email->fromAddress($inputs['from']);
                    }
                    $email->now();
                    if (Sent::sent == $email->send()) {
                        $sendone = true;
                    }
                }
                if ($sendone) {
                    $this->response->setStatus(true);
                    $this->response->Go(userpanel\url('email/sent'));
                } else {
                    throw new SendException();
                }
            } catch (InputValidationException $error) {
                $view->setFormError(FormError::fromException($error));
            } catch (SendException $error) {
                $error = new Error();
                $error->setCode('email.send');
                $view->addError($error);
            }
            $view->setDataForm($this->inputsvalue($inputsRules));
        } else {
            $this->response->setStatus(true);
            $inputsRules = [
                'user' => [
                    'type' => 'number',
                    'optional' => true,
                ],
                'to' => [
                    'type' => 'email',
                    'optional' => true,
                ],
                'forward' => [
                    'type' => 'number',
                    'optional' => true,
                ],
                'type' => [
                    'values' => ['get', 'sent'],
                    'optional' => true,
                ],
            ];
            $inputs = $this->checkinputs($inputsRules);
            foreach (array_keys($inputsRules) as $item) {
                if (isset($inputs[$item]) and '' == $inputs[$item]) {
                    unset($inputs[$item]);
                }
            }
            if (isset($inputs['user'])) {
                if ($user = User::byId($inputs['user'])) {
                    $view->setDataForm($user->email, 'to');
                }
            } elseif (isset($inputs['to'])) {
                $view->setDataForm($inputs['to'], 'to');
            }
            if (isset($inputs['forward'])) {
                if (isset($inputs['type'])) {
                    $types = Authorization::childrenTypes();
                    switch ($inputs['type']) {
                        case 'get':
                            Authorization::haveOrFail('get_list');
                            $get = new Get();
                            $get->where('id', $inputs['forward']);
                            if ($get = $get->getOne()) {
                                $view->setDataForm("FWD: {$get->subject}", 'subject');
                                $view->setDataForm($get->html, 'html');
                            }
                            break;
                        case 'sent':
                            Authorization::haveOrFail('sent_list');
                            $sent = new Sent();
                            db::join('userpanel_users', 'userpanel_users.id=email_sent.receiver_user', 'inner');
                            if ($types) {
                                $sent->where('userpanel_users.type', $types, 'in');
                            } else {
                                $sent->where('userpanel_users.id', Authentication::getID());
                            }
                            $sent->where('email_sent.id', $inputs['forward']);
                            if ($sent = $sent->getOne('email_sent.*')) {
                                $view->setDataForm("FWD: {$sent->subject}", 'subject');
                                $view->setDataForm($sent->html, 'html');
                            }
                            break;
                    }
                }
            }
        }
        $this->response->setView($view);

        return $this->response;
    }

    public function get_view($data)
    {
        Authorization::haveOrFail('get_view');
        $email = $this->getGetQuery()->byId($data['email']);
        if (!$email) {
            throw new NotFound();
        }
        $view = View::byName(views\get\view::class);
        $view->setEmail($email);

        $this->response->setStatus(true);
        if ($email->html) {
            $content = $email->html;
            $allows = '<html><head><body><p><a><b><strong><i><div><u><ul><li><ol><img><audio><video><span><section><aside><meta><form><button><input><h1><h2><h3><h4><h5><h6><style><small><table><tbody><thead><th><td><tr><option><select><fieldset>';
            $content = strip_tags($content, $allows);
            if (!http::getURIData('externalFiles')) {
                $content = preg_replace('/src\=(?:\"([^\"]+)\"|\'([^\']+)\')/', 'src=""', $content);
                $content = preg_replace('/\@import[^\"|^\'|^\;]+/', '#', $content);
            }
            $view->setContent($content);
            $view->isHTML();
            $view->hasExternalFiles((bool) http::getURIData('externalFiles'));
        } else {
            $view->setContent($email->text);
            $view->isText();
        }
        $this->response->setView($view);

        return $this->response;
    }

    public function sent_view($data)
    {
        Authorization::haveOrFail('sent_view');
        $email = $this->getSentQuery()->byId($data['email']);
        if (!$email) {
            throw new NotFound();
        }
        $view = View::byName(views\sent\view::class);
        $view->setEmail($email);

        $this->response->setStatus(true);
        if ($email->html) {
            $view->setContent($email->html);
            $view->isHTML();
        } else {
            $view->setContent($email->text);
            $view->isText();
        }
        $this->response->setView($view);

        return $this->response;
    }

    protected function getGetQuery(): Get
    {
        $types = Authorization::childrenTypes();
        $canAccessAnonymousEmails = Authorization::is_accessed('get_list_anonymous');
        $me = Authentication::getID();

        $query = new Get();
        if ($canAccessAnonymousEmails) {
            db::join('userpanel_users', 'userpanel_users.id=email_get.sender_user', 'left');
            $parenthesis = new Parenthesis();
            $parenthesis->where('userpanel_users.type', $types, 'in');
            $parenthesis->where('email_get.sender_user', null, 'is', 'or');
            $query->where($parenthesis);
        } else {
            db::join('userpanel_users', 'userpanel_users.id=email_get.sender_user', 'inner');
            if ($types) {
                $query->where('userpanel_users.type', $types, 'in');
            } else {
                $query->where('userpanel_users.id', $me);
            }
        }

        return $query;
    }

    protected function getSentQuery(): Sent
    {
        $types = Authorization::childrenTypes();
        $canAccessAnonymousEmails = Authorization::is_accessed('sent_list_anonymous');
        $me = Authentication::getID();

        $query = new Sent();
        if ($canAccessAnonymousEmails) {
            db::join('userpanel_users', 'userpanel_users.id=email_sent.receiver_user', 'left');
            $parenthesis = new Parenthesis();
            $parenthesis->where('userpanel_users.type', $types, 'in');
            $parenthesis->where('email_sent.receiver_user', null, 'is', 'or');
            $query->where($parenthesis);
        } else {
            db::join('userpanel_users', 'userpanel_users.id=email_sent.receiver_user', 'inner');
            if ($types) {
                $query->where('userpanel_users.type', $types, 'in');
            } else {
                $query->where('userpanel_users.id', $me);
            }
        }

        return $query;
    }
}
class SendException extends \Exception
{
}
