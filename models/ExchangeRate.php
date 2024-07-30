<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * ExchangeRate Model
 *
 * @property int $id
 * @property string $from_currency
 * @property string $to_currency
 * @property float $rate
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
            'delete' => true
        ],
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
     * deleteOld
     */
    public static function deleteOld()
    {
        // @todo this should look at data
        // $date = Carbon::now()->subDays(90);
        // static::query()->where('created_at', '<', $date)->delete();
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
        $fromCurrency = Currency::getPrimary();
        if (!$fromCurrency) {
            return;
        }

        $currencies = Currency::all();
        $existing = static::where('from_currency_code', $fromCurrency->currency_code)->pluck('to_currency_code')->all();

        foreach ($currencies as $toCurrency) {
            if ($fromCurrency->currency_code == $toCurrency->currency_code) {
                continue;
            }

            if (in_array($toCurrency->currency_code, $existing)) {
                continue;
            }

            $missing = new static;
            $missing->from_currency_code = $fromCurrency->currency_code;
            $missing->to_currency_code = $toCurrency->currency_code;
            $missing->save();
        }
    }
}
