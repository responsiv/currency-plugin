<?php namespace Responsiv\Currency\Models;

use Model;
use Cache;
use ValidationException;
use SystemException;

/**
 * Currency Model
 *
 * @property int $id
 * @property string $name
 * @property string $currency_code
 * @property string $currency_symbol
 * @property string $decimal_point
 * @property int $decimal_scale
 * @property string $thousand_separator
 * @property bool $place_symbol_before
 * @property bool $is_enabled
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class Currency extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'responsiv_currency_currencies';

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'currency_code' => 'required',
    ];

    /**
     * @var array cacheByCode cache of self, by code.
     */
    protected static $cacheByCode = [];

    /**
     * @var array cacheListEnabled is a cache of enabled currencies.
     */
    protected static $cacheListEnabled;

    /**
     * @var array cacheListAvailable is a cache of available currencies.
     */
    protected static $cacheListAvailable;

    /**
     * @var static primaryCurrency is default currency cache.
     */
    protected static $primaryCurrency;

    /**
     * syncPrimaryCurrency
     */
    public static function syncPrimaryCurrency()
    {
        if (static::count() > 0) {
            return;
        }

        $currency = new static;
        $currency->name = 'US Dollar';
        $currency->currency_code = 'USD';
        $currency->currency_symbol = '$';
        $currency->decimal_point = '.';
        $currency->decimal_scale = 2;
        $currency->thousand_separator = ',';
        $currency->place_symbol_before = true;
        $currency->is_primary = true;
        $currency->is_enabled = true;
        $currency->save();
    }

    /**
     * formatCurrency supplied currency to supplied settings.
     * @param  mixed  $number
     * @param  integer $decimals
     * @param  bool $baseValue
     * @return string
     */
    public function formatCurrency($number, $decimals = null, $baseValue = true)
    {
        if (!strlen($number)) {
            return null;
        }

        $negative = $number < 0;
        $negativeSymbol = null;

        if ($negative) {
            $number *= -1;
            $negativeSymbol = '-';
        }

        if ($baseValue) {
            $number = $this->fromBaseValue((int) $number);
        }

        $number = number_format(
            $number,
            $this->decimal_scale,
            $decimals === null ? $this->decimal_point : $decimals,
            $this->thousand_separator
        );

        if ($this->place_symbol_before) {
            return $negativeSymbol.$this->currency_symbol.$number;
        }
        else {
            return $negativeSymbol.$number.$this->currency_symbol;
        }
    }

    /**
     * afterCreate
     */
    public function afterCreate()
    {
        if ($this->is_primary) {
            $this->makePrimary();
        }
    }

    /**
     * beforeUpdate
     */
    public function beforeUpdate()
    {
        if ($this->isDirty('is_primary')) {
            $this->makePrimary();

            if (!$this->is_primary) {
                throw new ValidationException(['is_primary' => __("':currency' is already default and cannot be unset as default.", ['currency'=>$this->name])]);
            }
        }
    }

    /**
     * makePrimary makes this model the default
     */
    public function makePrimary()
    {
        if (!$this->is_enabled) {
            throw new ValidationException(['is_enabled' => __("':currency' is disabled and cannot be set as default.", ['currency'=>$this->name])]);
        }

        $this->newQuery()->where('id', $this->id)->update(['is_primary' => true]);
        $this->newQuery()->where('id', '<>', $this->id)->update(['is_primary' => false]);
    }

    /**
     * getPrimary returns the default currency defined.
     */
    public static function getPrimary(): ?static
    {
        if (self::$primaryCurrency !== null) {
            return self::$primaryCurrency;
        }

        $currency = self::where('is_primary', true)
            ->remember(1440, 'responsiv.currency.primaryCurrency')
            ->first()
        ;

        if (!$currency) {
            throw new SystemException('A primary currency was not found. Please set one up in the currency settings.');
        }

        return self::$primaryCurrency = $currency;
    }

    /**
     * getPrimaryCode
     */
    public static function getPrimaryCode(): ?string
    {
        return static::getPrimary()?->currency_code;
    }

    /**
     * findByCode locates a currency table by its code, cached.
     */
    public static function findByCode(string $code = null): ?static
    {
        if (!$code) {
            return null;
        }

        if (isset(self::$cacheByCode[$code])) {
            return self::$cacheByCode[$code];
        }

        return self::$cacheByCode[$code] = self::where('currency_code', $code)->first();
    }

    /**
     * scopeApplyEnabled for checking if model is enabled
     * @param  \October\Rain\Database\Builder $query
     * @return \October\Rain\Database\Builder
     */
    public function scopeApplyEnabled($query)
    {
        return $query
            ->whereNotNull('is_enabled')
            ->where('is_enabled', true)
        ;
    }

    /**
     * isAvailable returns true if there are at least 2 currencies available.
     */
    public static function isAvailable(): bool
    {
        return count(self::listAvailable()) > 1;
    }

    /**
     * listAvailable currencies, used on the back-end.
     */
    public static function listAvailable(): array
    {
        if (self::$cacheListAvailable) {
            return self::$cacheListAvailable;
        }

        return self::$cacheListAvailable = self::lists('name', 'currency_code');
    }

    /**
     * listEnabled currencies, used on the front-end.
     */
    public static function listEnabled(): array
    {
        if (self::$cacheListEnabled) {
            return self::$cacheListEnabled;
        }

        $isEnabled = Cache::remember('responsiv.currency.currencies', 1440, function() {
            return self::applyEnabled()->lists('name', 'currency_code');
        });

        return self::$cacheListEnabled = $isEnabled;
    }

    /**
     * isValid returns true if the supplied currency is valid.
     */
    public static function isValid($currency): bool
    {
        $currencies = array_keys(Currency::listEnabled());

        return in_array($currency, $currencies);
    }

    /**
     * toBaseValue converts a float to a base value stored in the database,
     * a base value has no decimal point.
     */
    public function toBaseValue(float $value): int
    {
        return $value * pow(10, (int) $this->decimal_scale);
    }

    /**
     * fromBaseValue converts from a base value to a float value from the database,
     * the returning value introduces a decimal point.
     */
    public function fromBaseValue(int $value): float
    {
        return $value / pow(10, (int) $this->decimal_scale);
    }

    /**
     * clearCache keys used by this model
     */
    public static function clearCache()
    {
        Cache::forget('responsiv.currency.currencies');
        Cache::forget('responsiv.currency.primaryCurrency');

        static::$cacheByCode = [];
        static::$cacheListEnabled = null;
        static::$cacheListAvailable = null;
        static::$primaryCurrency = null;
    }
}
