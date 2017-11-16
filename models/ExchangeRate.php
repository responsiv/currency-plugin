<?php namespace Responsiv\Currency\Models;

use Model;
use Carbon\Carbon;

/**
 * Exchange Rate Model
 */
class ExchangeRate extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_exchange_rates';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    public static function deleteOld()
    {
        $date = Carbon::now()->subDays(90);
        $this->newQuery()->where('created_at', '<', $date)->delete();
    }
}
