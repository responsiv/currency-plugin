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
        'enable_or_disable_title' => 'Enable or Disable Currencies',
        'enabled_label' => 'Enabled',
        'enabled_help' => 'Disabled currencies are not visible on the front-end.',
        'enable_or_disable' => 'Enable or disable',
        'selected_amount' => 'Currencies selected: :amount',
        'enable_success' => 'Successfully enabled those currencies.',
        'disable_success' => 'Successfully disabled those currencies.',
        'name' => 'Name',
        'name_help' => 'Human readable name',
        'code' => 'Code',
        'is_primary' => 'Default',
        'is_primary_help' => 'Use this as the default currency.',
        'is_enabled' => 'Enabled',
        'is_enabled_help' => 'Make currency available.',
        'currency_code' => 'Currency code',
        'currency_code_help' => 'International currency code, e.g. USD',
        'currency_symbol' => 'Symbol',
        'currency_symbol_help' => 'Symbol to put beside amount, e.g. $',
        'decimal_point' => 'Decimal Point',
        'decimal_point_help' => 'Character to use as decimal point',
        'thousand_separator' => 'Thousand Separator',
        'thousand_separator_help' => 'Character to separate thousands',
        'place_symbol_before' => 'Place symbol before number',
        'not_available_help' => 'There are no other currencies set up.',
        'hint_currencies' => 'Create new currencies here for translating front-end content. The default currency represents the content before it has been translated.',
        'new_currency' => 'New Currency',
        'example' => 'Example',
        'currencies' => 'Currencies',
        'description' => 'Create and configure available currencies.'
    ],
    'converter' => [
        'class_name' => 'Converter',
        'refresh_interval' => 'Update interval',
        'title' => 'Currency converters',
        'description' => 'Select and manage the currency converter to use.'
    ]
];
