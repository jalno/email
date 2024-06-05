<?php

namespace packages\email\Listeners\Settings;

use packages\userpanel\UserType\Permissions;

class UserType
{
    public function permissions_list()
    {
        $permissions = [
            'sent_list',
            'sent_list_anonymous',
            'get_list',
            'get_list_anonymous',
            'send',
            'settings_senders_list',
            'settings_senders_add',
            'settings_senders_edit',
            'settings_senders_delete',
            'settings_receivers_list',
            'settings_receivers_add',
            'settings_receivers_edit',
            'settings_receivers_delete',
            'settings_templates_list',
            'settings_templates_add',
            'settings_templates_edit',
            'settings_templates_delete',
            'get_view',
            'sent_view',
        ];
        foreach ($permissions as $permission) {
            Permissions::add('email_'.$permission);
        }
    }
}
