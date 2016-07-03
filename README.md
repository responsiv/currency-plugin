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

For example, to convert an amount from USD to BTC:

    Currency::format(10, ['from' => 'USD', 'to' => 'BTC']);

To display a currency in long or short format

    // $10.00 USD
    Currency::format(10, ['format' => 'long']);

    // $10
    Currency::format(10, ['format' => 'short']);
