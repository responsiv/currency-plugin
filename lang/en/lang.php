<?php

return [
    'plugin' => [
        'name' => 'Currency',
        'description' => 'Tools for currency display and conversion.',
        'tab' => 'Currency',
        'manage_currencies' => 'Manage currencies',
    ],
    'currency' => [
        'title' => 'Manage currencies',
        'update_title' => 'Update currency',
        'create_title' => 'Create currency',
        'select_label' => 'Select currency',
        'unset_default' => '":currency" is already default and cannot be unset as default.',
        'disabled_default' => '":currency" is disabled and cannot be set as default.',
        'name' => 'Name',
        'code' => 'Code',
        'is_primary' => 'Default',
        'is_primary_help' => 'Use this as the default currency.',
        'is_enabled' => 'Enabled',
        'is_enabled_help' => 'Make currency available.',
        'not_available_help' => 'There are no other currencies set up.',
        'hint_currencies' => 'Create new currencies here for translating front-end content. The default currency represents the content before it has been translated.',
    ],
];