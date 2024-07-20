<?php

namespace packages\email\Controllers\Settings;

use packages\base\DB\Parenthesis;
use packages\base\Http;
use packages\base\InputValidation;
use packages\base\NotFound;
use packages\base\View\Error;
use packages\base\Views\FormError;
use packages\email\Authorization;
use packages\email\Controller;
use packages\email\Imap\Exception as ImapException;
use packages\email\Receiver;
use packages\email\View;
use packages\userpanel;

class Receivers extends Controller
{
    protected $authentication = true;

    public function listreceivers()
    {
        Authorization::haveOrFail('settings_receivers_list');
        $view = View::byName(\packages\email\Views\Settings\Receivers\ListView::class);
        $receiver = new Receiver();
        $inputsRules = [
            'id' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'title' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'hostname' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'port' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'username' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'type' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'encryption' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'status' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'word' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'comparison' => [
                'values' => ['equals', 'startswith', 'contains'],
                'default' => 'contains',
                'optional' => true,
            ],
        ];
        $this->response->setStatus(true);
        try {
            $inputs = $this->checkinputs($inputsRules);
            if (isset($inputs['type']) and 0 != $inputs['type']) {
                if (!in_array($inputs['type'], [Receiver::IMAP, Receiver::POP3, Receiver::NNTP])) {
                    throw new InputValidation('type');
                }
            }
            if (isset($inputs['encryption']) and 0 != $inputs['encryption']) {
                if (!in_array($inputs['encryption'], [Receiver::SSL, Receiver::TLS])) {
                    throw new InputValidation('encryption');
                }
            }
            if (isset($inputs['status']) and 0 != $inputs['status']) {
                if (!in_array($inputs['status'], [Receiver::active, Receiver::deactive])) {
                    throw new InputValidation('status');
                }
            }

            foreach (['id', 'title', 'hostname', 'port', 'username', 'type', 'encryption', 'status'] as $item) {
                if (isset($inputs[$item]) and $inputs[$item]) {
                    $comparison = $inputs['comparison'];
                    if (in_array($item, ['id', 'port', 'type', 'encryption', 'status'])) {
                        $comparison = 'equals';
                    }
                    $receiver->where($item, $inputs[$item], $comparison);
                }
            }
            if (isset($inputs['word']) and $inputs['word']) {
                $parenthesis = new Parenthesis();
                foreach (['title', 'hostname', 'username'] as $item) {
                    if (!isset($inputs[$item]) or !$inputs[$item]) {
                        $parenthesis->where($item, $inputs['word'], $inputs['comparison'], 'OR');
                    }
                }
                $receiver->where($parenthesis);
            }
        } catch (InputValidation $error) {
            $view->setFormError(FormError::fromException($error));
            $this->response->setStatus(false);
        }
        $view->setDataForm($this->inputsvalue($inputsRules));
        $receiver->orderBy('id', 'ASC');
        $receiver->pageLimit = $this->items_per_page;
        $items = $receiver->paginate($this->page);
        $view->setPaginate($this->page, $receiver->totalCount, $this->items_per_page);
        $view->setDataList($items);
        $this->response->setView($view);

        return $this->response;
    }

    public function add()
    {
        Authorization::haveOrFail('settings_receivers_add');
        $view = View::byName(\packages\email\Views\Settings\Receivers\Add::class);
        if (HTTP::is_post()) {
            $inputsRules = [
                'title' => [
                    'type' => 'string',
                ],
                'hostname' => [
                    'regex' => '/^([a-z0-9\\-]+\\.)+[a-z]{2,12}$/i',
                ],
                'port' => [
                    'type' => 'number',
                ],
                'type' => [
                    'values' => [Receiver::IMAP, Receiver::POP3, Receiver::NNTP],
                ],
                'encryption' => [
                    'values' => [Receiver::SSL, Receiver::TLS],
                    'empty' => true,
                ],
                'username' => [
                    'type' => 'string',
                ],
                'password' => [
                    'type' => 'string',
                ],
                'status' => [
                    'type' => 'number',
                    'values' => [Receiver::active, Receiver::deactive],
                ],
            ];
            $this->response->setStatus(false);
            try {
                $inputs = $this->checkinputs($inputsRules);

                if ($inputs['port'] < 1 or $inputs['port'] > 65535) {
                    throw new InputValidation('port');
                }

                $receiver = new Receiver();
                $receiver->title = $inputs['title'];
                $receiver->type = $inputs['type'];
                $receiver->hostname = $inputs['hostname'];
                $receiver->port = $inputs['port'];
                $receiver->type = $inputs['type'];
                $receiver->encryption = $inputs['encryption'];
                $receiver->username = $inputs['username'];
                $receiver->password = $inputs['password'];
                $receiver->status = $inputs['status'];
                $receiver->check();
                $receiver->save();
                $this->response->setStatus(true);
                $this->response->Go(userpanel\url('settings/email/receivers/edit/'.$receiver->id));
            } catch (InputValidation $error) {
                $view->setFormError(FormError::fromException($error));
            } catch (ImapException $e) {
                $error = new Error();
                $error->setCode('emails.receiver.connect');
                $error->setType(Error::FATAL);
                $view->addError($error);
            }
            $view->setDataForm($this->inputsvalue($inputsRules));
        } else {
            $this->response->setStatus(true);
        }
        $this->response->setView($view);

        return $this->response;
    }

    public function delete($data)
    {
        Authorization::haveOrFail('settings_receivers_delete');
        $receiver = (new Receiver())->byID($data['receiver']);
        if (!$receiver) {
            throw new NotFound();
        }
        $view = View::byName(\packages\email\Views\Settings\Receivers\Delete::class);
        $view->setReceiver($receiver);
        if (HTTP::is_post()) {
            $receiver->delete();

            $this->response->setStatus(true);
            $this->response->Go(userpanel\url('settings/email/receivers'));
        } else {
            $this->response->setStatus(true);
        }
        $this->response->setView($view);

        return $this->response;
    }

    public function edit($data)
    {
        Authorization::haveOrFail('settings_receivers_edit');
        $receiver = (new Receiver())->byID($data['receiver']);
        if (!$receiver) {
            throw new NotFound();
        }
        $view = View::byName(\packages\email\Views\Settings\Receivers\Edit::class);
        $view->setReceiver($receiver);
        if (HTTP::is_post()) {
            $inputsRules = [
                'title' => [
                    'type' => 'string',
                    'optional' => true,
                ],
                'hostname' => [
                    'regex' => '/^([a-z0-9\\-]+\\.)+[a-z]{2,12}$/i',
                    'optional' => true,
                ],
                'port' => [
                    'type' => 'number',
                    'optional' => true,
                ],
                'type' => [
                    'values' => [Receiver::IMAP, Receiver::POP3, Receiver::NNTP],
                    'optional' => true,
                ],
                'encryption' => [
                    'values' => [Receiver::SSL, Receiver::TLS],
                    'empty' => true,
                    'optional' => true,
                ],
                'username' => [
                    'type' => 'string',
                    'optional' => true,
                ],
                'password' => [
                    'type' => 'string',
                    'optional' => true,
                ],
                'status' => [
                    'type' => 'number',
                    'values' => [Receiver::active, Receiver::deactive],
                    'optional' => true,
                ],
            ];
            $this->response->setStatus(false);
            try {
                $inputs = $this->checkinputs($inputsRules);
                if (array_key_exists('port', $inputs)) {
                    if ($inputs['port'] < 1 or $inputs['port'] > 65535) {
                        throw new InputValidation('port');
                    }
                }
                foreach ([
                    'title',
                    'type',
                    'hostname',
                    'port',
                    'type',
                    'encryption',
                    'username',
                    'password',
                    'status0',
                ] as $input) {
                    if (array_key_exists($input, $inputs)) {
                        $receiver->$input = $inputs[$input];
                    }
                }
                $receiver->check();
                $receiver->save();
                $this->response->setStatus(true);
            } catch (InputValidation $error) {
                $view->setFormError(FormError::fromException($error));
            } catch (ImapException $e) {
                $error = new Error();
                $error->setCode('emails.receiver.connect');
                $error->setType(Error::FATAL);
                $view->addError($error);
            }
            $view->setDataForm($this->inputsvalue($inputsRules));
        } else {
            $this->response->setStatus(true);
        }
        $this->response->setView($view);

        return $this->response;
    }
}
