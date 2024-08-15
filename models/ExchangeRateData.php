<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * ExchangeRateData Model
 *
 * @property int $id
 * @property int $rate_id
 * @property float $rate_value
 * @property \Illuminate\Support\Carbon $valid_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 */
class ExchangeRateData extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'responsiv_currency_exchange_rate_data';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    /**
     * @var array belongsTo
     */
    public $belongsTo = [
        'rate' => ExchangeRate::class
    ];

    /**
     * afterSave
     */
    public function afterSave()
    {
        if ($rate = $this->rate) {
            $rate->updateRateValue();
        }
    }
}
