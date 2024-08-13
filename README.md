# Currency plugin

Tools for dealing with currency display and conversions. You can configure currencies and converters via the Settings page.

- Settings → Currencies
- Settings → Currency Rates
- Settings → Site Definitions

## Official Documentation

This plugin is partially documented in the official October CMS documentation.

Article | Purpose
------- | --------
[Currency Twig Filter](https://docs.octobercms.com/3.x/markup/filter/currency.md) | Formatting Currency in Twig markup
[Currency Form Widget](https://docs.octobercms.com/3.x/element/form/widget-currency.md) | Displaying currency as an input field
[Currency List Column](https://docs.octobercms.com/3.x/element/lists/column-currency.md) | Displaying currency in a list column

## Understanding Currency Definitions

There are multiple currency definition types that are important to operation the Currency plugin. Each definition type is described in more detail below.

### Default Currency

The default currency is set by opening the **Settings → Currencies** page and checking the **Default** checkbox on a currency listed on this page.

The default currency is used when there is no multisite context or when there is no currency set in one of the other definitions. In the currency form widget, if the model does not implement the multisite feature, then the value is stored in the default currency.

### Primary / Base Currency

The primary currency is a base currency set by opening the **Settings → Site Definitions** page and selecting a currency in the **Base Currency** dropdown.

The display currency is available in Twig as `this.site.base_currency` and `this.site.base_currency_code`.

```twig
{{ this.site.base_currency_code }}
```

The primary currency sets the currency to use for writing values in a multisite context. For example, if the model implements the multisite feature, then the value is stored in the primary currency set by the active site.

```twig
{{ product.price|currency({ from: this.site.base_currency_code })}}
```

### Display Currency

The primary currency is set by opening the **Settings → Site Definitions** page and selecting a currency in the **Display Currency** dropdown.

The display currency is available in Twig as `this.site.currency` and `this.site.currency_code`.

```twig
{{ this.site.currency_code }}
```

The display currency has a specific purpose of converting a currency from its stored value before displaying it. For example, if a value is stored in the default currency as USD and the site definition has a display currency of AUD.

```twig
{{ product.price|currency({ to: this.site.currency_code })}}
```

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
