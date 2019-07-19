<?php

return [
    'plugin' => [
        'name' => 'Valuta',
        'description' => 'Tool per visualizzare e convertire valute.',
        'tab' => 'Valuta',
        'manage_currencies' => 'Gestisci le valute',
    ],
    'currency' => [
        'title' => 'Gestisci valute',
        'update_title' => 'Aggiorna valuta',
        'create_title' => 'Crea valuta',
        'select_label' => 'Seleziona valuta',
        'unset_default' => '":currency" è già default e non può essere impostata come non default.',
        'disabled_default' => '":currency" è disabilitata e quindi non può essere impostata come default.',
        'enable_or_disable_title' => 'Abilita o Disabilita Valute',
        'enabled_label' => 'Abilitata',
        'enabled_help' => 'Le valute disabilitate non verranno mostrate nel front-end.',
        'enable_or_disable' => 'Abilita o Disabilita',
        'selected_amount' => 'Valuta selezionata: :amount',
        'enable_success' => 'Valute selezionate abilitate correttamente.',
        'disable_success' => 'Valute selezionate disabilitate correttamente.',
        'name' => 'Nome',
        'name_help' => 'Nome per esteso della valuta',
        'code' => 'Codice',
        'is_primary' => 'Default',
        'is_primary_help' => 'Usa questa valuta come valuta di default.',
        'is_enabled' => 'Abilitata',
        'is_enabled_help' => 'Rendi disponibile questa valuta.',
        'currency_code' => 'Codice valuta',
        'currency_code_help' => 'Codice Internazionale della valuta, es. USD',
        'currency_symbol' => 'Simbolo',
        'currency_symbol_help' => 'Simbolo da visualizzare della valuta, es. $',
        'decimal_point' => 'Separatore Decimali',
        'decimal_point_help' => 'Carattere da utilizzare per separare le cifre decimali',
        'thousand_separator' => 'Separatore Migliaia',
        'thousand_separator_help' => 'Carattere da utilizzare per separare le migliaia',
        'place_symbol_before' => 'Simbolo prima del numero',
        'not_available_help' => 'Non ci sono ulteriori valute configurate.',
        'hint_currencies' => 'Crea qui nuove valute per la tradurre il contenuto del front-end. La valuta di default rappresenta il contenuto prima che esso venga tradotto.',
        'new_currency' => 'Nuova Valuta',
        'example' => 'Esempio',
        'currencies' => 'Valute',
        'description' => 'Crea e configura le valute disponibili.'
    ],
    'converter' => [
        'class_name' => 'Convertitore',
        'refresh_interval' => 'Intervallo di Aggiornamento',
        'title' => 'Convertitore Valute',
        'description' => 'Seleziona e gestisci il convertitore di valute da utilizzare.'
    ]
];
