# Currency plugin

Tools for dealing with currency display and conversions. You can configure currencies and converters via the Settings page.

- Settings → Currencies
- Settings → Exchange Rates

## Get Started

A quick start guide for this plugin can be found at the following link:

- [Announcing the Release of the Currency Plugin v2.0
](https://octobercms.com/blog/post/announcing-release-currency-plugin-v2)

## Official Documentation

This plugin is partially documented in the official October CMS documentation.

Article | Purpose
------- | --------
[Currency Twig Filter](https://docs.octobercms.com/3.x/markup/filter/currency.html) | Formatting Currency in Twig markup
[Currency Form Widget](https://docs.octobercms.com/3.x/element/form/widget-currency.html) | Displaying currency as an input field
[Currency List Column](https://docs.octobercms.com/3.x/element/lists/column-currency.html) | Displaying currency in a list column

## Understanding Currency Definitions

There are multiple currency definition types that are important to operating the Currency plugin. Each definition type is described in more detail below.

### Default Currency

The default currency is the global anchor — all prices are stored in this currency. It is used when there is no site context or when no other currency is configured. In the currency form widget, if the model does not implement the multisite feature, the value is stored in the default currency.

> **Note**: The default currency is set by opening the **Settings → Currencies** page and checking the **Default** checkbox on a currency listed on this page.

### Base Currency (Site Group)

The base currency can be set on a **Site Group** to override the stored currency for all sites in that group. This is an edge case for installations where different groups of sites store prices in different currencies (e.g. a US group stores in USD while a UK group stores in GBP).

When no base currency is set on the site group, the global default currency is used.

> **Note**: The base currency is set by opening the **Settings → Site Groups** page and selecting a currency in the **Base Currency** dropdown.

### Site Currency

The site currency defines what currency is displayed to users visiting that site. When set, the currency filter automatically converts from the base currency to the site currency using exchange rates.

The site currency is available in Twig as `this.site.currency` and `this.site.currency_code`.

```twig
{{ this.site.currency_code }}
```

For example, if a value is stored in the default currency as USD and the site has a currency of AUD, the `site` option handles conversion automatically.

```twig
{{ product.price|currency({ site: true }) }}
```

> **Note**: The site currency is set by opening the **Settings → Site Definitions** page and selecting a currency in the **Currency** dropdown.

## Currencyable Trait

The `Currencyable` trait allows models to store explicit per-currency price overrides in a sidecar table. It uses attribute interception (similar to the Translatable trait) so the model's `$attributes` always hold primary currency values. When a non-primary currency is active, reads return the explicit override if one exists, or fall back to exchange-rate conversion automatically.

### Setup

Add the trait and define which attributes support currency overrides:

```php
class Product extends Model
{
    use \Responsiv\Currency\Traits\Currencyable;

    public $currencyable = ['price', 'cost'];
}
```

### How It Works

1. **Attribute interception**: `getAttribute()` and `setAttribute()` are overridden. When the active currency differs from the primary, reads and writes are redirected to the sidecar cache instead of `$attributes`.
2. **Exchange-rate fallback**: When no explicit override exists for a currency, `getCurrencyOverride()` automatically converts the base value using `Currency::convert()`.
3. **Before save**: `syncCurrencyableAttributes()` persists any dirty sidecar data and restores the original primary-currency values on the model so the main table is never written with override values.
4. **Clearing overrides**: Setting a currency override to null or empty deletes the sidecar row, reverting the attribute to automatic exchange-rate conversion.

### Storage

Overrides are stored in the `responsiv_currency_attributes` table:

Column | Purpose
------ | -------
`model_type` | Morph type (model class)
`model_id` | Foreign key to the model
`currency_code` | Currency code (e.g. `EUR`, `GBP`)
`attribute` | Attribute name (e.g. `price`)
`value` | Override value

### Reading Overrides

```php
// Get override for a specific currency (falls back to exchange-rate conversion)
$eurPrice = $product->getCurrencyOverride('price', 'EUR');

// Get override without fallback (returns null if no override)
$eurPrice = $product->getCurrencyOverride('price', 'EUR', false);

// Check if an explicit override exists (not exchange-rate converted)
if ($product->hasCurrencyOverride('price', 'EUR')) {
    // ...
}

// Get all stored currency values for an attribute
$allPrices = $product->getCurrencyOverrides('price');
// Returns: ['USD' => 1000, 'EUR' => 950, 'GBP' => 800]

// Get the primary-currency value (always reads from $attributes)
$basePrice = $product->getCurrencyableBaseValue('price');
```

### Writing Overrides

```php
// Set a single override
$product->setCurrencyOverride('price', 'EUR', 950);

// Set multiple currencies at once
$product->setCurrencyOverrides('price', [
    'EUR' => 950,
    'GBP' => 800,
]);

// Remove an override (reverts to exchange rate conversion)
$product->forgetCurrencyOverride('price', 'EUR');

// Remove all overrides for an attribute
$product->forgetCurrencyOverrides('price');

// Remove all overrides for a currency
$product->forgetAllCurrencyOverrides('EUR');
```

### Context Switching

```php
// Override the currency context for a model instance
$product->setCurrency('EUR');
echo $product->price; // Returns EUR override or exchange-rate converted value

// Get the active currency code
$code = $product->getCurrency();
```

### Query Scopes

```php
// Filter by currency override value
Product::whereCurrencyOverride('price', 'EUR', 950)->get();

// Eager load overrides for the active currency
Product::withCurrencyOverride()->get();

// Eager load overrides for a specific currency
Product::withCurrencyOverride('EUR')->get();

// Eager load all currency overrides
Product::withCurrencyOverrides()->get();
```

### Currency Resolution

The trait resolves currencies through the `CurrencyManager`:

Method | Returns | Purpose
------ | ------- | -------
`getCurrencyableDefault()` | Base currency code | Site group's base currency, or global default
`getCurrencyableContext()` | Active currency code | Site's currency, or falls back to base
`shouldConvertCurrency()` | `bool` | `true` when active differs from base

### Enabling / Disabling

The trait is enabled by default. Override `isCurrencyableEnabled()` in the model to disable it conditionally:

```php
public function isCurrencyableEnabled()
{
    return false; // Disable currency overrides for this model
}
```

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
