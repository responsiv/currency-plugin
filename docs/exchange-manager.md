# Exchange Manager

The `Responsiv\Currency\Classes\ExchangeManager` instance is used to manage currency exchange and conversion. Use the `instance` method to create an instance of the exchange manager.

```php
$manager = ExchangeManager::instance();
```

## Currency Conversion

Currency converters are registered as exchange types the `registerCurrencyConverters` method override in the plugin registration file.

- [Learn more about Building Exchange Types](./building-exchange-types.md)

## Currency Exchange

The `getRate` method will return an exchange rate for a currency pair, containing a `$fromCurrency` and `$toCurrency`. For example, the exchange rate from USD to AUD. If an exchange rate is not found, the reverse conversion will be attempted.

```php
// Returns the exchange rate from USD to AUD
$manager->getRate('USD', 'AUD');
```

The `requestAllRates` will spin over every configured currency converter and request the most recent rates. Pairs are stored against the `Responsiv\Currency\Models\ExchangeRate` model.

```php
$manager->requestAllRates();
```
