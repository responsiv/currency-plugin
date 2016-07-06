<?php

return [
    'plugin' => [
        'name' => 'Валюта',
        'description' => 'Отображение валют и их конвертация по текущему курсу',
        'tab' => 'Валюта',
        'manage_currencies' => 'Управление валютами',
    ],
    'currency' => [
        'title' => 'Управление валютами',
        'update_title' => 'Обновить валюту',
        'create_title' => 'Создать валюту',
        'select_label' => 'Выбрать валюту',
        'unset_default' => '":currency" используется по умолчанию и не может быть удалена',
        'disabled_default' => '":currency" отключена и не может быть установлена по умолчанию.',
        'enable_or_disable_title' => 'Включение/отключение валюты',
        'enabled_label' => 'Включена',
        'enabled_help' => 'Отключенные валюты не будут отображаться во фронтенде',
        'enable_or_disable' => 'Включить или отключить',
        'selected_amount' => 'Выбранная валюта: :amount',
        'enable_success' => 'Валюта успешно включена',
        'disable_success' => 'Валюта успешно выключена.',
        'name' => 'Название',
        'code' => 'Код',
        'is_primary' => 'По умолчанию',
        'is_primary_help' => 'Станет основной валютой',
        'is_enabled' => 'Включено',
        'is_enabled_help' => 'Сделать валюту доступной.',
        'currency_code' => 'Код валюты',
        'currency_code_help' => 'По международному стандарту, например: RUB или USD',
        'currency_symbol' => 'Символ',
        'currency_symbol_help' => 'Символ отоброжаемый рядом с валютой например: &#8381;',
        'decimal_point' => 'Разделитель копеек',
        'decimal_point_help' => 'Отделяет от копеек',
        'thousand_separator' => 'Разделитель тысяч',
        'thousand_separator_help' => 'Символ для разделения тысяч',
        'place_symbol_before' => 'Поместить символ перед суммой',
        'not_available_help' => 'There are no other currencies set up.',
        'hint_currencies' => 'Create new currencies here for translating front-end content. The default currency represents the content before it has been translated.',
    ],
    'converter' => [
        'class_name' => 'Ковертер',
        'refresh_interval' => 'Интервал обновления курсов валют'
    ]
];
