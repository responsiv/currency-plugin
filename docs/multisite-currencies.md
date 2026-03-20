# Multisite Currencies

The Currency plugin integrates with October CMS multisite to display and manage values in different currencies across multiple sites. This guide explains how to configure multisite currency support for your application.

## Understanding Currency Tiers

The Currency plugin uses a three-tier currency system. Each tier serves a different purpose and falls back to the tier above it.

### Default Currency

The default currency is the global anchor — all exchange rates are defined relative to this currency. It is used when no site context is available or when no other currency is configured.

To set the default currency, navigate to **Settings → Currencies** and check the **Default** checkbox on the desired currency.

### Base Currency (Site Group)

The base currency can be set on a **Site Group** to define the stored currency for all sites in that group. This is useful when different groups of sites use different currencies — for example, a US group uses USD while a UK group uses GBP.

When no base currency is set on the site group, the global default currency is used.

To set a base currency, navigate to **Settings → Site Groups** and select a currency in the **Base Currency** dropdown.

### Site Currency

The site currency defines what currency is displayed to users visiting that site. It is the "active" currency in the frontend context. When set, the currency filter automatically converts from the base currency to the site currency using exchange rates.

To set a site currency, navigate to **Settings → Site Definitions** and select a currency in the **Currency** dropdown.

## Setup Steps

Follow these steps to configure multisite currencies for your application.

### 1. Create Currencies

Navigate to **Settings → Currencies** and create the currencies your application will use. Mark one currency as the **Default** — this will be the global anchor for all exchange rate conversions.

### 2. Configure Exchange Rates

Navigate to **Settings → Exchange Rates** and set up currency pairs for conversion. You can either:

- **Set fixed rates manually** using the Fixed Rate exchange type
- **Use an automated provider** such as Fixer.io or FastForex for real-time rates

Exchange rates are used as the automatic fallback when no explicit override is set for a currency.

### 3. Set Base Currency on Site Groups

If your site groups use different currencies, navigate to **Settings → Site Groups** and select a **Base Currency** for each group. This step is optional — when left unset, the global default currency is used.

### 4. Set Currency on Site Definitions

Navigate to **Settings → Site Definitions** and set the **Currency** for each site. This determines what currency is displayed to users visiting that site.

For example:

Site | Locale | Currency
---- | ------ | --------
English (AU) | `en` | AUD
French (FR) | `fr` | EUR
German (DE) | `de` | EUR

## Using the Currencyable Trait

The `Currencyable` trait allows models to store explicit per-currency overrides in a sidecar table. Add the trait to any model that needs multi-currency values.

```php
class MyModel extends Model
{
    use \Responsiv\Currency\Traits\Currencyable;

    public $currencyable = ['price', 'cost'];
}
```

### How Value Resolution Works

When a currencyable attribute is read, the following logic applies:

1. If the active currency matches the base currency, the raw database value is returned
2. If an explicit override exists for the active currency, the override value is returned
3. If no override exists, the base value is automatically converted using exchange rates

This means currencyable attributes always have a value — either an explicit override or an automatic conversion.

### Admin Form Behavior

The `currency` form widget automatically detects currencyable attributes and adjusts its behavior:

- **On the primary currency site** (e.g. English/AUD): A normal editable input field in the base currency
- **On a non-primary currency site** (e.g. French/EUR): A disabled input showing the auto-converted value. An **Override** link enables manual entry for a fixed value. A **Clear** link removes the override and reverts to exchange-rate conversion

This design prevents accidental data corruption — admins cannot accidentally save a base-currency value with a non-base currency symbol.

### Frontend Display

Use the `currency` Twig filter with the `site` option to convert and format values for the visitor's active site currency.

```twig
{{ model.price|currency({ site: true }) }}
```

The site currency is also available in Twig templates:

```twig
{{ this.site.currency_code }}
```

## Working with Overrides

### Setting Overrides Programmatically

```php
// Set a fixed EUR value
$model->setCurrencyOverride('price', 'EUR', 2500);

// Set multiple currencies at once
$model->setCurrencyOverrides('price', [
    'EUR' => 2500,
    'GBP' => 2100,
]);

// Remove an override (reverts to exchange rate)
$model->forgetCurrencyOverride('price', 'EUR');
```

### Reading Overrides

```php
// Get the value in EUR (override or converted)
$eurPrice = $model->getCurrencyOverride('price', 'EUR');

// Check if an explicit override exists
if ($model->hasCurrencyOverride('price', 'EUR')) {
    // This is a fixed value, not converted
}

// Get the base currency value
$baseValue = $model->getCurrencyableBaseValue('price');
```

### Context Switching

```php
// Switch a model instance to a different currency
$model->setCurrency('EUR');
echo $model->price; // Returns EUR value
```

## Storage

Currency overrides are stored in the `responsiv_currency_attributes` sidecar table. The model's main database columns always hold the base (primary) currency values. This separation ensures that currency overrides never corrupt the canonical data.
