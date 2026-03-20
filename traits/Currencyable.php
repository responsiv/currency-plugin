<?php namespace Responsiv\Currency\Traits;

use Db;
use Currency;

/**
 * Currencyable trait provides per-row model currency overrides using a
 * single currency attributes table. Currency is resolved via CurrencyManager.
 *
 * Uses getAttribute/setAttribute interception (sidecar pattern) instead of
 * promote/demote. The model's $attributes always hold primary currency values.
 * Non-primary currency reads are served from the sidecar cache with
 * exchange-rate conversion as fallback when no explicit override exists.
 *
 * Usage:
 *
 *     use \Responsiv\Currency\Traits\Currencyable;
 *
 *     public $currencyable = ['price', 'cost'];
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
trait Currencyable
{
    /**
     * @var string currencyableContext is the active currency code override
     */
    protected $currencyableContext;

    /**
     * @var string currencyableDefault is the default/primary currency code cache
     */
    protected $currencyableDefault;

    /**
     * @var array currencyableAttributes stores loaded currency data keyed by currency code
     */
    protected $currencyableAttributes = [];

    /**
     * @var array currencyableOriginals stores original currency data for dirty checking
     */
    protected $currencyableOriginals = [];

    /**
     * initializeCurrencyable trait for a model
     */
    public function initializeCurrencyable()
    {
        if (!is_array($this->currencyable)) {
            throw new \Exception(sprintf(
                'The $currencyable property in %s must be an array to use the Currencyable trait.',
                static::class
            ));
        }

        $this->morphMany['currency_overrides'] = [
            \Responsiv\Currency\Models\CurrencyAttribute::class,
            'name' => 'model',
            'delete' => true
        ];

        $this->bindEvent('model.saveInternal', function() {
            $this->syncCurrencyableAttributes();
        });
    }

    //
    // Currency resolution
    //

    /**
     * getCurrencyableContext returns the active currency code, resolved lazily
     */
    public function getCurrencyableContext()
    {
        if ($this->currencyableContext === null) {
            $this->currencyableContext = $this->resolveCurrencyableCode();
        }

        return $this->currencyableContext;
    }

    /**
     * getCurrencyableDefault returns the primary/default currency code, resolved lazily
     */
    public function getCurrencyableDefault()
    {
        if ($this->currencyableDefault === null) {
            $this->currencyableDefault = $this->resolveCurrencyableDefaultCode();
        }

        return $this->currencyableDefault;
    }

    /**
     * resolveCurrencyableCode reads the active currency code from CurrencyManager
     */
    protected function resolveCurrencyableCode()
    {
        return Currency::getActiveCode();
    }

    /**
     * resolveCurrencyableDefaultCode reads the primary (base) currency code from
     * CurrencyManager. This is the currency prices are stored in — respects the
     * site group's base currency override if set, otherwise the global default.
     */
    protected function resolveCurrencyableDefaultCode()
    {
        return Currency::getPrimaryCode();
    }

    //
    // Activation & bypass
    //

    /**
     * isCurrencyableEnabled returns true when currency conversion should be
     * active for this model. Override this in the model to disable.
     */
    public function isCurrencyableEnabled()
    {
        return true;
    }

    /**
     * shouldConvertCurrency returns true when the active currency differs from the primary.
     * Returns false for single-currency installs so the trait is invisible.
     */
    public function shouldConvertCurrency()
    {
        if (!$this->isCurrencyableEnabled()) {
            return false;
        }

        return $this->getCurrencyableContext() !== $this->getCurrencyableDefault();
    }

    /**
     * isCurrencyableAttribute checks if a specific attribute is currencyable
     */
    public function isCurrencyableAttribute($key)
    {
        return in_array($key, $this->getCurrencyableAttributes());
    }

    /**
     * getCurrencyableAttributes returns the currencyable attribute names
     */
    public function getCurrencyableAttributes()
    {
        return $this->currencyable;
    }

    //
    // Attribute interception
    //

    /**
     * getAttribute overrides the parent to return converted/overridden values when active
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatedCurrencyAttribute($key)) {
            return $this->getCurrencyOverride($key, $this->getCurrencyableContext());
        }

        return parent::getAttribute($key);
    }

    /**
     * setAttribute overrides the parent to store currency override values when active
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatedCurrencyAttribute($key)) {
            return $this->setCurrencyOverride($key, $this->getCurrencyableContext(), $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * isTranslatedCurrencyAttribute checks if a specific attribute should be
     * currency-converted right now. Returns false when the primary currency is
     * active or the attribute is not in $currencyable.
     */
    protected function isTranslatedCurrencyAttribute($key)
    {
        if ($key === 'currencyable' || !$this->shouldConvertCurrency()) {
            return false;
        }

        return in_array($key, $this->getCurrencyableAttributes());
    }

    //
    // Base value access
    //

    /**
     * getCurrencyableBaseValue returns the primary-currency value for a currencyable
     * attribute, always reading from $attributes (which holds primary values).
     */
    public function getCurrencyableBaseValue(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    //
    // Reading currency overrides
    //

    /**
     * getCurrencyOverride returns the override value for an attribute and currency code.
     * Falls back to exchange-rate conversion when no explicit override exists.
     */
    public function getCurrencyOverride($key, $currencyCode, $useFallback = true)
    {
        // Primary currency reads from model attributes
        if ($currencyCode === $this->getCurrencyableDefault()) {
            return $this->attributes[$key] ?? null;
        }

        if (!array_key_exists($currencyCode, $this->currencyableAttributes)) {
            $this->loadCurrencyableData($currencyCode);
        }

        if ($this->hasCurrencyOverride($key, $currencyCode)) {
            return $this->currencyableAttributes[$currencyCode][$key] ?? null;
        }

        if ($useFallback) {
            $baseValue = $this->attributes[$key] ?? null;
            if ($baseValue !== null) {
                return Currency::convert($baseValue, $currencyCode, $this->getCurrencyableDefault());
            }
        }

        return null;
    }

    /**
     * getCurrencyOverrides returns all currency values for a single attribute
     */
    public function getCurrencyOverrides($key)
    {
        $overrides = [];

        // Primary currency from model attributes
        $defaultCode = $this->getCurrencyableDefault();
        $defaultValue = $this->attributes[$key] ?? null;
        if ($defaultValue !== null) {
            $overrides[$defaultCode] = $defaultValue;
        }

        // Other currencies from currency_overrides relation
        $rows = $this->currency_overrides->where('attribute', $key);
        foreach ($rows as $row) {
            $overrides[$row->currency_code] = $row->value;
        }

        return $overrides;
    }

    /**
     * hasCurrencyOverride checks if an explicit override exists for one attribute.
     * Returns false for exchange-rate converted values.
     */
    public function hasCurrencyOverride($key, $currencyCode = null)
    {
        if ($currencyCode === null) {
            $currencyCode = $this->getCurrencyableContext();
        }

        // Primary currency always has the value in model attributes
        if ($currencyCode === $this->getCurrencyableDefault()) {
            $value = $this->attributes[$key] ?? null;
            return $value !== null && $value !== '';
        }

        if (!array_key_exists($currencyCode, $this->currencyableAttributes)) {
            $this->loadCurrencyableData($currencyCode);
        }

        $value = $this->currencyableAttributes[$currencyCode][$key] ?? null;

        return $value !== null && $value !== '';
    }

    //
    // Writing currency overrides
    //

    /**
     * setCurrencyOverride sets an override value for an attribute and currency code
     */
    public function setCurrencyOverride($key, $currencyCode, $value)
    {
        if ($currencyCode === $this->getCurrencyableDefault()) {
            $this->attributes[$key] = $value;
            return $value;
        }

        // For new records ensure the base attributes are populated
        if (!$this->exists && !array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = $value;
        }

        if (!array_key_exists($currencyCode, $this->currencyableAttributes)) {
            $this->loadCurrencyableData($currencyCode);
        }

        $this->currencyableAttributes[$currencyCode][$key] = $value;

        return $value;
    }

    /**
     * setCurrencyOverrides sets multiple currency values at once for a single attribute
     */
    public function setCurrencyOverrides($key, array $overrides)
    {
        foreach ($overrides as $currencyCode => $value) {
            $this->setCurrencyOverride($key, $currencyCode, $value);
        }
    }

    //
    // Deleting currency overrides
    //

    /**
     * forgetCurrencyOverride deletes a single currency override row
     */
    public function forgetCurrencyOverride($key, $currencyCode)
    {
        Db::table($this->getCurrencyAttributeTable())
            ->where('model_type', $this->getMorphClass())
            ->where('model_id', $this->getKey())
            ->where('currency_code', $currencyCode)
            ->where('attribute', $key)
            ->delete();

        unset($this->currencyableAttributes[$currencyCode][$key]);
        unset($this->currencyableOriginals[$currencyCode][$key]);
    }

    /**
     * forgetCurrencyOverrides deletes all currency override rows for an attribute
     */
    public function forgetCurrencyOverrides($key)
    {
        Db::table($this->getCurrencyAttributeTable())
            ->where('model_type', $this->getMorphClass())
            ->where('model_id', $this->getKey())
            ->where('attribute', $key)
            ->delete();

        foreach ($this->currencyableAttributes as $currencyCode => &$data) {
            unset($data[$key]);
        }

        foreach ($this->currencyableOriginals as $currencyCode => &$data) {
            unset($data[$key]);
        }
    }

    /**
     * forgetAllCurrencyOverrides deletes all overrides for a currency code
     */
    public function forgetAllCurrencyOverrides($currencyCode)
    {
        Db::table($this->getCurrencyAttributeTable())
            ->where('model_type', $this->getMorphClass())
            ->where('model_id', $this->getKey())
            ->where('currency_code', $currencyCode)
            ->delete();

        unset($this->currencyableAttributes[$currencyCode]);
        unset($this->currencyableOriginals[$currencyCode]);
    }

    //
    // Currency context
    //

    /**
     * setCurrency overrides the currency context for this model instance
     */
    public function setCurrency($currencyCode)
    {
        $this->currencyableContext = $currencyCode;

        $this->fireEvent('model.currency.contextChange', [$currencyCode]);

        return $this;
    }

    /**
     * getCurrency returns the active currency code
     */
    public function getCurrency()
    {
        return $this->getCurrencyableContext();
    }

    //
    // Dirty checking
    //

    /**
     * isCurrencyDirty determines if the model or a given currency attribute
     * has been modified for a currency code
     */
    public function isCurrencyDirty($attribute = null, $currencyCode = null)
    {
        $dirty = $this->getCurrencyDirty($currencyCode);

        if (is_null($attribute)) {
            return count($dirty) > 0;
        }

        return array_key_exists($attribute, $dirty);
    }

    /**
     * getCurrencyDirty returns the currency attributes that have been changed
     */
    public function getCurrencyDirty($currencyCode = null)
    {
        if (!$currencyCode) {
            $currencyCode = $this->getCurrencyableContext();
        }

        if (!array_key_exists($currencyCode, $this->currencyableAttributes)) {
            return [];
        }

        // All dirty when no originals recorded
        if (!array_key_exists($currencyCode, $this->currencyableOriginals)) {
            return $this->currencyableAttributes[$currencyCode];
        }

        $dirty = [];
        foreach ($this->currencyableAttributes[$currencyCode] as $key => $value) {
            if (!array_key_exists($key, $this->currencyableOriginals[$currencyCode])) {
                $dirty[$key] = $value;
            }
            elseif ($value != $this->currencyableOriginals[$currencyCode][$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    //
    // Data storage
    //

    /**
     * syncCurrencyableAttributes stores currency overrides and restores
     * original primary-currency values on the model before the DB write
     */
    protected function syncCurrencyableAttributes()
    {
        // Store overrides. When the model has no key yet, defer until after insert
        if ($this->getKey()) {
            $this->storeCurrencyableBasicData();
        }
        else {
            $this->bindEventOnce('model.saveComplete', function() {
                $this->storeCurrencyableBasicData();
            });
        }

        // Saving the default currency, no need to restore anything
        if (!$this->shouldConvertCurrency()) {
            return;
        }

        // Restore currencyable values to model originals so the base model
        // saves its own attributes correctly (not the override values)
        $original = $this->getOriginal();
        $attributes = $this->getAttributes();
        $currencyable = $this->getCurrencyableAttributes();
        $originalValues = array_intersect_key($original, array_flip($currencyable));
        $this->attributes = array_merge($attributes, $originalValues);
    }

    /**
     * storeCurrencyableBasicData stores overrides for each known dirty currency
     */
    protected function storeCurrencyableBasicData()
    {
        $knownCurrencies = array_keys($this->currencyableAttributes);
        foreach ($knownCurrencies as $currencyCode) {
            if (!$this->isCurrencyDirty(null, $currencyCode)) {
                continue;
            }

            $this->storeCurrencyableData($currencyCode);
        }
    }

    /**
     * storeCurrencyableData saves currency override data for a single currency using upsert
     */
    protected function storeCurrencyableData($currencyCode)
    {
        $dirty = $this->getCurrencyDirty($currencyCode);

        if (empty($dirty)) {
            return;
        }

        $isPrimaryCurrency = ($currencyCode === $this->getCurrencyableDefault());

        $rows = [];
        foreach ($dirty as $key => $value) {
            // For non-primary currencies, null/empty value means "use exchange rate"
            // so delete any existing override row
            if (!$isPrimaryCurrency && ($value === null || $value === '')) {
                $this->forgetCurrencyOverride($key, $currencyCode);
                continue;
            }

            // For non-primary currencies, skip attributes whose value matches the
            // base value. No row = no override, auto-conversion handles it.
            if (!$isPrimaryCurrency) {
                $defaultValue = $this->attributes[$key] ?? null;
                if ($value === $defaultValue) {
                    continue;
                }
            }

            $rows[] = [
                'model_type' => $this->getMorphClass(),
                'model_id' => $this->getKey(),
                'currency_code' => $currencyCode,
                'attribute' => $key,
                'value' => $value,
            ];
        }

        if (empty($rows)) {
            return;
        }

        Db::table($this->getCurrencyAttributeTable())->upsert(
            $rows,
            ['model_type', 'model_id', 'currency_code', 'attribute'],
            ['value']
        );
    }

    /**
     * loadCurrencyableData loads currency override data for a currency code
     */
    protected function loadCurrencyableData($currencyCode)
    {
        if ($this->relationLoaded('currency_overrides')) {
            $rows = $this->currency_overrides
                ->where('currency_code', $currencyCode)
                ->pluck('value', 'attribute')
                ->toArray();
        }
        else {
            $rows = Db::table($this->getCurrencyAttributeTable())
                ->where('model_type', $this->getMorphClass())
                ->where('model_id', $this->getKey())
                ->where('currency_code', $currencyCode)
                ->pluck('value', 'attribute')
                ->toArray();
        }

        $this->currencyableAttributes[$currencyCode] = $rows;
        $this->currencyableOriginals[$currencyCode] = $rows;
    }

    //
    // Query scopes
    //

    /**
     * scopeWhereCurrencyOverride adds a where clause for a currency override attribute
     */
    public function scopeWhereCurrencyOverride($query, $key, $currencyCode, $value, $operator = '=')
    {
        return $query->whereExists(function ($q) use ($key, $currencyCode, $value, $operator) {
            $table = $this->getCurrencyAttributeTable();

            $q->select(Db::raw(1))
                ->from($table)
                ->whereColumn($table . '.model_id', $this->getQualifiedKeyName())
                ->where($table . '.model_type', $this->getMorphClass())
                ->where($table . '.currency_code', $currencyCode)
                ->where($table . '.attribute', $key)
                ->where($table . '.value', $operator, $value);
        });
    }

    /**
     * scopeWithCurrencyOverride eager loads overrides for a single currency
     */
    public function scopeWithCurrencyOverride($query, $currencyCode = null)
    {
        if ($currencyCode === null) {
            $currencyCode = $this->getCurrencyableContext();
        }

        return $query->with(['currency_overrides' => function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        }]);
    }

    /**
     * scopeWithCurrencyOverrides eager loads all currency overrides
     */
    public function scopeWithCurrencyOverrides($query)
    {
        return $query->with('currency_overrides');
    }

    //
    // Helpers
    //

    /**
     * getCurrencyAttributeTable returns the table name for currency override storage
     */
    public function getCurrencyAttributeTable()
    {
        return (new \Responsiv\Currency\Models\CurrencyAttribute)->getTable();
    }
}