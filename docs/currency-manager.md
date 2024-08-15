# Currency Manager

There is a `Currency` facade you may use for common currency management tasks. This facade resolves to the `Responsiv\Currency\Classes\CurrencyManager` instance by default.

You may use the `convert` method on the `Currency` facade to convert a currency value.

```php
// Converts the default currency to AUD
Currency::convert(100, 'AUD');

// Converts explicitly the USD currency to AUD
Currency::convert(100, 'AUD', 'USD');
```

The `getDefault` returns the default currency model, and `getDefaultCode` returns the default currency code.

```php
$default = Currency::getDefault();

$defaultCode = Currency::getDefaultCode();
```

The `getPrimary` returns the default currency model, and `getPrimaryCode` returns the default currency code.

```php
$primary = Currency::getPrimary();

$primaryCode = Currency::getPrimaryCode();
```

The `getActive` returns the default currency model, and `getActiveCode` returns the default currency code.

```php
$active = Currency::getActive();

$activeCode = Currency::getActiveCode();
```

## Currency Model

A currency object represents a `Responsiv\Currency\Models\Currency` model object.

The `fromBaseValue` converts a currency, for example, converts 100 to 1.00.

```php
// Returns 1.00
$currency->fromBaseValue(100);
```

The `toBaseValue` converts a currency to a base value, for example, converts 1.00 to 100.

```php
// Returns 100
$currency->toBaseValue(1.00);
```
