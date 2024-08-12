# Building Exchange Types

Exchange types are currency conversion providers defined as classes located in the **exchangetypes** directory of this plugin. You can create your own plugins with this directory or place them inside the `app` directory.

```
plugins/
  acme/
    myplugin/
      exchangetypes/
        fixer/           <=== Class Directory
          fields.yaml    <=== Field Configuration
        Fixer.php        <=== Class File
      Plugin.php
```

These instructions can be used to create your own exchange type classes to integrate with specific gateways, and a plugin can be host to many exchange types, not just one.

## Payment Type Definition

Payment type classes should extend the `Responsiv\Currency\Classes\ExchangeBase` class, which is an abstract PHP class containing all the necessary methods for implementing a exchange type. By extending this base class, you can add the necessary features, such as communicating with the the currency converter API.

The exchange type from the next example should be defined in the **plugins/acme/myplugin/exchangetypes/Fixer.php** file. Aside from the PHP file, exchange types can also have a directory that matches the PHP file name. If the class name is **Fixer.php** then the corresponding directory name is **fixer**. These class directories can contain partials and form field configuration used by the exchange type.

```php
class Fixer extends GatewayBase
{
    public function driverDetails()
    {
        return [
            'name' => 'Fixer',
            'description' => 'Currency exchange rate service provided by Fixer.io'
        ];
    }

    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        // ...
    }
}
```

The `driverDetails` method is required. The method should return an array with two keys: name and description. The name and description are display in the administration panel when setting up the exchange type.

Payment types must be registered by overriding the `registerCurrencyConverters` method inside the plugin registration file (Plugin.php). This tells the system about the exchange type and provides a short code for referencing it.

The following registers the `Fixer` class with the code **fixer* so it is ready to use.

```php
public function registerCurrencyConverters()
{
    return [
        \Responsiv\Pay\PaymentTypes\Fixer::class => 'fixer',
    ];
}
```

## Building the Payment Configuration Form

By default, the exchange type will look for its form field definitions as a file **fields.yaml** in the class directory. In this file, you can define [form fields and tabs](https://docs.octobercms.com/3.x/element/form-fields.html) used by the exchange type configuration form.

When a exchange type is selected for configuration, it is stored as a `Responsiv\Pay\Models\PaymentMethod` instance. All field values are saved automatically to this model and are available inside the processing code.

The configuration form fields can add things like API usernames and passwords used for the payment gateway. The following might be stored in the **plugins/acme/myplugin/exchangetypes/fixer/fields.yaml** file.

```yaml
fields:
    access_key:
        label: API Access Key
        comment: Specify the unique key assigned to your Fixer account.
        span: auto
        tab: Configuration

    use_secure_endpoint:
        label: Use Secure Endpoint
        comment: Some subscriptions do not support HTTPS so uncheck this to disable encryption.
        type: checkbox
        tab: Configuration
```

### Initializing the Configuration Form

You may initialize the values of the configuration form fields by overriding the `initDriverHost` method in the exchange type class definition. The method takes a `$host` argument as the model object, which can be used to set the attribute values matching the form fields.

The following example checks if the model is newly created using `$host->exists` and sets some default values.

```php
public function initDriverHost($host)
{
    if (!$host->exists) {
        $host->name = 'Fixer';
        $host->use_secure_endpoint = false;
    }
}
```

### Validating the Configuration Form

Once you have the form fields specified, you may wish to validate their input. Override the `validateDriverHost` method inside the class to implement validation logic. The method takes a `$host` argument as a model object with the attributes matching those found in the form field definition.

Throw the `\ValidationException` exception to trigger a validation error message. This exception takes an array with the field name as a key and the error message as the value. The message should use the `__()` helper function to enable localization for the message.

```php
public function validateDriverHost($host)
{
    if (!$host->access_key) {
        throw new \ValidationException(['access_key' => __("Please specify an Access Key")]);
    }
}
```

For simple validation rules, you can apply them to the model using the `initDriverHost` method.

```php
public function initDriverHost($host)
{
    $host->rules['access_key'] = 'required';
}
```

## Requesting Exchange Rates from a Provider

When the currency plugin needs to find an exchange rate, it invokes the `getExchangeRate` method of a corresponding exchange type class. This method sends a request to the service provider to request the latest rate, using the source and destination currency, along with any API keys to authenticate the request.

The method should be defined in the following way and takes two arguments, the source `$fromCurrency` and destination `$toCurrency` passed as currency codes. For example, from **USD** to **AUD**.

```php
public function getExchangeRate($fromCurrency, $toCurrency)
{
    // ...
}
```

The contents of the `getExchangeRate` depends on the specific exchange provider's requirements, however the method should integrate with the API documentation provided. If an error occurs, the `\SystemException` can be thrown to log an invalid responses, or the `\ApplicationException` can be used for unlogged errors.

```php
public function getExchangeRate($fromCurrency, $toCurrency)
{
    $fromCurrency = trim(strtoupper($fromCurrency));
    $toCurrency = trim(strtoupper($toCurrency));

    $response = $this->requestRatesFromService($fromCurrency);
    if (!$response) {
        throw new \SystemException('Error loading the Fixer currency exchange feed.');
    }

    $rates = $response['rates'] ?? [];
    if (!$rates) {
        throw new \SystemException('The Fixer currency exchange rate service returned invalid data.');
    }

    if (!$rate = array_get($rates, $toCurrency)) {
        throw new \SystemException('The Fixer currency exchange rate service is missing the destination currency.');
    }

    return $rate;
}
```

If the exchange rate should not provide any currency exchange data, for example, when the currency pair is not supported, it can return the `\Responsiv\Currency\Models\ExchangeConverter::NO_RATE_DATA` constant to gracefully inform the process that no exchange rate was found for the requested pair.

```php
public function getExchangeRate($fromCurrency, $toCurrency)
{
    return \Responsiv\Currency\Models\ExchangeConverter::NO_RATE_DATA;
}
```
