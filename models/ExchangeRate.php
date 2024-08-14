<?php namespace Responsiv\Currency\Models;

use Date;
use Model;
use Currency as CurrencyService;

/**
 * ExchangeRate Model
 *
 * @property int $id
 * @property string $from_currency_code
 * @property string $to_currency_code
 * @property float $rate_value
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class ExchangeRate extends Model
{
    /**
     * @var string table associated with the model
     */
    public $table = 'responsiv_currency_exchange_rates';

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    /**
     * @var array hasMany
     */
    public $hasMany = [
        'rate_data' => [
            ExchangeRateData::class,
            'key' => 'rate_id',
            'order' => 'valid_at desc',
            'delete' => true
        ],
    ];

    /**
     * @var array belongsTo
     */
    public $belongsTo = [
        'converter' => ExchangeConverter::class
    ];

    /**
     * getFromCurrencyCodeOptions
     */
    public function getFromCurrencyCodeOptions()
    {
        return Currency::listAvailable();
    }

    /**
     * getToCurrencyCodeOptions
     */
    public function getToCurrencyCodeOptions()
    {
        return Currency::listAvailable();
    }

    /**
     * beforeSave
     */
    public function beforeSave()
    {
        if (!$this->rate_value) {
            $this->rate_value = 1;
        }
    }

    /**
     * deleteOld deletes records created 90 days or older
     */
    public function deleteOld()
    {
        $date = Date::now()->subDays(90);
        $this->rate_data()->where('created_at', '<', $date)->delete();
    }

    /**
     * updateRateValue sets the latest rate value on this exchange rate
     */
    public function updateRateValue()
    {
        if ($recentRate = $this->rate_data()->orderBy('valid_at', 'desc')->first()) {
            $this->rate_value = $recentRate->rate_value;
            $this->save();
        }
    }

    /**
     * getPairCodeAttribute
     */
    public function getPairCodeAttribute()
    {
        return "{$this->from_currency_code}:{$this->to_currency_code}";
    }

    /**
     * generatePairs
     */
    public static function generatePairs()
    {
        $count = 0;
        $fromCurrency = CurrencyService::getPrimary();
        if (!$fromCurrency) {
            return;
        }

        $currencies = Currency::listEnabled();
        $existing = static::where('from_currency_code', $fromCurrency->code)->pluck('to_currency_code')->all();

        foreach ($currencies as $toCurrency) {
            if ($fromCurrency->code == $toCurrency->code) {
                continue;
            }

            if (in_array($toCurrency->code, $existing)) {
                continue;
            }

            $missing = new static;
            $missing->from_currency_code = $fromCurrency->code;
            $missing->to_currency_code = $toCurrency->code;
            $missing->save();
            $count++;
        }

        return $count;
    }
}
