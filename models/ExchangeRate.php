<?php namespace Responsiv\Currency\Models;

use Model;
use Carbon\Carbon;

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
     * deleteOld
     * @deprecated
     */
    public static function deleteOld()
    {
        $date = Carbon::now()->subDays(90);
        static::query()->where('created_at', '<', $date)->delete();
    }

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
}
