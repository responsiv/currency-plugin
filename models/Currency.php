<?php namespace Responsiv\Currency\Models;

use Model;
use Cache;
use ValidationException;

/**
 * Currency Model
 *
 * @property int $id
 * @property string $name
 * @property string $code
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
    use \System\Traits\KeyCodeModel;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Defaultable;
    use \Responsiv\Currency\Models\Currency\HasBaseValues;
    use \Responsiv\Currency\Models\Currency\HasPluckHelpers;

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
        'code' => 'required',
    ];

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
        $currency->code = 'USD';
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
            $number = $this->fromBaseValueRaw((int) $number);
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
        return static::getDefault()?->code;
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
        return count(self::getCodeList()) > 1;
    }

    /**
     * isValid returns true if the supplied currency is valid.
     */
    public static function isValid($currency): bool
    {
        $currencies = array_keys(Currency::getCodeList());

        return in_array($currency, $currencies);
    }

    /**
     * clearCache keys used by this model
     */
    public static function clearCache()
    {
        Cache::forget('responsiv.currency.currencies');

        static::$cacheByKey = [];
        static::$cacheByCode = [];
        static::$nameList = null;
        static::$codeList = null;
        static::$codeListAll = null;
    }

    /**
     * @deprecated use `Currency::getPrimary()`
     */
    public static function getPrimary()
    {
        return \Currency::getPrimary();
    }

    /**
     * @deprecated use `$this->code`
     */
    public function getCurrencyCodeAttribute()
    {
        return $this->code;
    }

    /**
     * @deprecated use `getAllCodeList`
     */
    public static function listAvailable(): array
    {
        return static::getAllCodeList();
    }

    /**
     * @deprecated use `getCodeList`
     */
    public static function listEnabled(): array
    {
        return static::getCodeList();
    }
}
