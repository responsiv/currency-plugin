<?php namespace Responsiv\Currency\Models;

use Lang;
use Model;
use Cache;
use ValidationException;

/**
 * Currency Model
 */
class Currency extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_currencies';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'currency_code' => 'required',
    ];

    public $timestamps = false;

    /**
     * @var array Object cache of self, by code.
     */
    protected static $cacheByCode = [];

    /**
     * @var array A cache of enabled currencies.
     */
    protected static $cacheListEnabled;

    /**
     * @var array A cache of available currencies.
     */
    protected static $cacheListAvailable;

    /**
     * @var self Default currency cache.
     */
    private static $primaryCurrency;

    /**
     * Formats supplied currency to supplied settings.
     * @param  mixed  $number   Currency amount
     * @param  integer $decimals Decimal places to include
     * @return string
     */
    public function formatCurrency($number, $decimals = 2)
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

        $number = number_format($number, $decimals, $this->decimal_point, $this->thousand_separator);

        if ($this->place_symbol_before) {
            return $negativeSymbol.$this->currency_symbol.$number;
        }
        else {
            return $negativeSymbol.$number.$this->currency_symbol;
        }
    }

    public function afterCreate()
    {
        if ($this->is_primary) {
            $this->makePrimary();
        }
    }

    public function beforeUpdate()
    {
        if ($this->isDirty('is_primary')) {
            $this->makePrimary();

            if (!$this->is_primary) {
                throw new ValidationException(['is_primary' => Lang::get('responsiv.currency::lang.currency.unset_default', ['currency'=>$this->name])]);
            }
        }
    }

    /**
     * Makes this model the default
     * @return void
     */
    public function makePrimary()
    {
        if (!$this->is_enabled) {
            throw new ValidationException(['is_enabled' => Lang::get('responsiv.currency::lang.currency.disabled_default', ['currency'=>$this->name])]);
        }

        $this->newQuery()->where('id', $this->id)->update(['is_primary' => true]);
        $this->newQuery()->where('id', '<>', $this->id)->update(['is_primary' => false]);
    }

    /**
     * Returns the default currency defined.
     * @return self
     */
    public static function getPrimary()
    {
        if (self::$primaryCurrency !== null) {
            return self::$primaryCurrency;
        }

        return self::$primaryCurrency = self::where('is_primary', true)
            ->remember(1440, 'responsiv.currency.primaryCurrency')
            ->first()
        ;
    }

    /**
     * Locate a currency table by its code, cached.
     * @param  string $code
     * @return Model
     */
    public static function findByCode($code = null)
    {
        if (!$code) {
            return null;
        }

        if (isset(self::$cacheByCode[$code])) {
            return self::$cacheByCode[$code];
        }

        return self::$cacheByCode[$code] = self::whereCurrencyCode($code)->first();
    }

    /**
     * Scope for checking if model is enabled
     * @param  \October\Rain\Database\Builder $query
     * @return \October\Rain\Database\Builder
     */
    public function scopeIsEnabled($query)
    {
        return $query
            ->whereNotNull('is_enabled')
            ->where('is_enabled', true)
        ;
    }

    /**
     * Returns true if there are at least 2 currencies available.
     * @return boolean
     */
    public static function isAvailable()
    {
        return count(self::listAvailable()) > 1;
    }

    /**
     * Lists available currencies, used on the back-end.
     * @return array
     */
    public static function listAvailable()
    {
        if (self::$cacheListAvailable) {
            return self::$cacheListAvailable;
        }

        return self::$cacheListAvailable = self::lists('name', 'currency_code');
    }

    /**
     * Lists the enabled currencies, used on the front-end.
     * @return array
     */
    public static function listEnabled()
    {
        if (self::$cacheListEnabled) {
            return self::$cacheListEnabled;
        }

        $isEnabled = Cache::remember('responsiv.currency.currencies', 1440, function() {
            return self::isEnabled()->lists('name', 'currency_code');
        });

        return self::$cacheListEnabled = $isEnabled;
    }

    /**
     * Returns true if the supplied currency is valid.
     * @return boolean
     */
    public static function isValid($currency)
    {
        $currencies = array_keys(Currency::listEnabled());

        return in_array($currency, $currencies);
    }

    /**
     * Clears all cache keys used by this model
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('responsiv.currency.currencies');
        Cache::forget('responsiv.currency.primaryCurrency');
    }
}
