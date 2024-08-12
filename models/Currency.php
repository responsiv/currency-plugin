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
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class Currency extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Defaultable;

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
     * @var array nameList cache for nameList() method
     */
    protected static $nameList = null;

    /**
     * @var array enabledCodeList is a cache of enabled currencies.
     */
    protected static $enabledCodeList;

    /**
     * @var array availableCodeList is a cache of available currencies.
     */
    protected static $availableCodeList;

    /**
     * syncDefaultCurrency
     */
    public static function syncDefaultCurrency()
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
        $currency->is_default = true;
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
            $decimals === null ? $this->decimal_scale : $decimals,
            $this->decimal_point,
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
     * beforeUpdate
     */
    public function beforeUpdate()
    {
        if ($this->isDirty('is_default') && !$this->is_default) {
            throw new ValidationException(['is_default' => __("':currency' is already default and cannot be unset as default.", ['currency'=>$this->name])]);
        }
    }

    /**
     * getDefaultCode
     */
    public static function getDefaultCode(): ?string
    {
        return static::getDefault()?->currency_code;
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
     * getNameList
     */
    public static function getNameList()
    {
        if (self::$nameList) {
            return self::$nameList;
        }

        return self::$nameList = self::applyEnabled()->lists('name', 'id');
    }

    /**
     * listAvailable currencies, used on the back-end.
     */
    public static function listAvailable(): array
    {
        if (self::$availableCodeList) {
            return self::$availableCodeList;
        }

        return self::$availableCodeList = self::lists('name', 'currency_code');
    }

    /**
     * listEnabled currencies, used on the front-end.
     */
    public static function listEnabled(): array
    {
        if (self::$enabledCodeList) {
            return self::$enabledCodeList;
        }

        $isEnabled = Cache::remember('responsiv.currency.currencies', 1440, function() {
            return self::applyEnabled()->lists('name', 'currency_code');
        });

        return self::$enabledCodeList = $isEnabled;
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

        static::$cacheByCode = [];
        static::$nameList = null;
        static::$enabledCodeList = null;
        static::$availableCodeList = null;
    }

    /**
     * @deprecated use `Currency::getPrimary()`
     */
    public static function getPrimary()
    {
        return \Currency::getPrimary();
    }
}
