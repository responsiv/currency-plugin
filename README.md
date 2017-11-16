# Currency plugin

Tools for dealing with currency display and conversions. You can configure currencies and converters via the Settings page.

- Settings \ Currencies
- Settings \ Currency converters

### Formatting currency

You may call the currency facade in PHP using `Currency::format` or in Twig using the `|currency` filter.

    <?= Currency::format(10) ?>

    {{ 10|currency }}

This method takes an options argument, as an array that supports various values.

* to: To a given currency code
* from: From a currency code
* format: Display format. Options: long, short, null.

For example, to convert an amount from USD to AUD:

    Currency::format(10, ['from' => 'USD', 'to' => 'AUD']);

To display a currency in long or short format

    // $10.00 USD
    Currency::format(10, ['format' => 'long']);

    // $10
    Currency::format(10, ['format' => 'short']);

### Currency Form Widget

This plugin introduces a currency form field called `currency`. The form widget renders a text field that displays the currency symbol. When the field is shown as a preview, the number is formatted using the primary currency settings.

Usage:

    # ===================================
    #  Form Field Definitions
    # ===================================

    fields:
        total_amount:
            label: Total amount
            type: currency
            format: short

### Currency List Column

This plugin introduces a currency list column called `currency`. The value is formatted using the primary currency settings.

    # ===================================
    #  List Column Definitions
    # ===================================

    columns:
        total_amount:
            label: Loan amount
            type: currency
