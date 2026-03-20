<?php namespace Responsiv\Currency\Tests;

use PluginTestCase;
use Responsiv\Currency\Models\Currency;
use Responsiv\Currency\Models\ExchangeRate;
use Responsiv\Currency\Classes\ExchangeManager;
use Responsiv\Currency\Classes\CurrencyManager;
use October\Rain\Database\Model;

/**
 * CurrencyConversionTest validates the exchange rate lookup, currency
 * conversion pipeline, base value conversions, pair generation, and
 * graceful handling of missing currency pairs.
 */
class CurrencyConversionTest extends PluginTestCase
{
    /**
     * setUp
     */
    public function setUp(): void
    {
        parent::setUp();

        // Clear caches between tests
        Currency::clearCache();
        ExchangeManager::instance()->rateCache = [];
    }

    //
    // Helpers
    //

    /**
     * createExchangeRate creates a currency pair with the given rate
     */
    protected function createExchangeRate(string $from, string $to, float $rate): ExchangeRate
    {
        Model::unguard();
        $exchangeRate = ExchangeRate::create([
            'from_currency_code' => $from,
            'to_currency_code' => $to,
            'rate_value' => $rate,
        ]);
        Model::reguard();

        return $exchangeRate;
    }

    //
    // Exchange Rate Lookup
    //

    /**
     * testSameCurrencyReturnsOne — converting USD to USD should
     * always return a rate of 1.0 without needing a pair record
     */
    public function testSameCurrencyReturnsOne()
    {
        $rate = ExchangeManager::instance()->getRate('USD', 'USD');
        $this->assertEquals(1.0, $rate);
    }

    /**
     * testDirectPairLookup — when a USD→EUR pair exists, the rate
     * should be returned directly
     */
    public function testDirectPairLookup()
    {
        $this->createExchangeRate('USD', 'EUR', 0.92);

        $rate = ExchangeManager::instance()->getRate('USD', 'EUR');
        $this->assertEquals(0.92, $rate);
    }

    /**
     * testInversePairFallback — when only USD→EUR exists, looking up
     * EUR→USD should return the reciprocal (1 / 0.92)
     */
    public function testInversePairFallback()
    {
        $this->createExchangeRate('USD', 'EUR', 0.92);

        $rate = ExchangeManager::instance()->getRate('EUR', 'USD');
        $this->assertEqualsWithDelta(1 / 0.92, $rate, 0.0001);
    }

    /**
     * testCaseInsensitiveLookup — currency codes should be normalized
     * to uppercase before lookup
     */
    public function testCaseInsensitiveLookup()
    {
        $this->createExchangeRate('USD', 'GBP', 0.79);

        $rate = ExchangeManager::instance()->getRate('usd', 'gbp');
        $this->assertEquals(0.79, $rate);
    }

    /**
     * testRateCaching — the same pair lookup should hit cache on
     * subsequent calls without additional DB queries
     */
    public function testRateCaching()
    {
        $this->createExchangeRate('USD', 'AUD', 1.53);

        $manager = ExchangeManager::instance();
        $rate1 = $manager->getRate('USD', 'AUD');
        $rate2 = $manager->getRate('USD', 'AUD');

        $this->assertEquals(1.53, $rate1);
        $this->assertEquals(1.53, $rate2);
        $this->assertArrayHasKey('USD_AUD', $manager->rateCache);
    }

    /**
     * testMissingPairReturnsFallbackRate — when no pair exists and no
     * inverse is found, getRate should return 1.0 and log a warning
     * instead of throwing an exception
     */
    public function testMissingPairReturnsFallbackRate()
    {
        $rate = ExchangeManager::instance()->getRate('USD', 'JPY');

        // Should return 1.0 (no-op conversion) instead of throwing
        $this->assertEquals(1.0, $rate);
    }

    //
    // Currency Conversion
    //

    /**
     * testConvertUsdToEur — basic conversion: $100 USD at 0.92 = €92
     */
    public function testConvertUsdToEur()
    {
        $this->createExchangeRate('USD', 'EUR', 0.92);

        $result = CurrencyManager::instance()->convert(10000, 'EUR', 'USD');
        $this->assertEquals(9200, $result);
    }

    /**
     * testConvertWithInverseRate — converting EUR to USD using only
     * the USD→EUR pair should use the reciprocal rate
     */
    public function testConvertWithInverseRate()
    {
        $this->createExchangeRate('USD', 'EUR', 0.80);

        // €8000 to USD: 8000 * (1/0.80) = 10000
        $result = CurrencyManager::instance()->convert(8000, 'USD', 'EUR');
        $this->assertEquals(10000, $result);
    }

    /**
     * testConvertSameCurrencyIsNoop — converting USD to USD should
     * return the original value unchanged
     */
    public function testConvertSameCurrencyIsNoop()
    {
        $result = CurrencyManager::instance()->convert(5000, 'USD', 'USD');
        $this->assertEquals(5000, $result);
    }

    /**
     * testConvertWithFractionalRate — real-world rates produce
     * fractional cent results: 10000 * 0.79 = 7900
     */
    public function testConvertWithFractionalRate()
    {
        $this->createExchangeRate('USD', 'GBP', 0.79);

        $result = CurrencyManager::instance()->convert(10000, 'GBP', 'USD');
        $this->assertEquals(7900, $result);
    }

    /**
     * testConvertWithSubCentPrecision — when the conversion produces
     * a non-integer result, the raw float should be returned (rounding
     * is the caller's responsibility)
     */
    public function testConvertWithSubCentPrecision()
    {
        $this->createExchangeRate('USD', 'EUR', 0.923);

        // 9999 * 0.923 = 9229.077
        $result = CurrencyManager::instance()->convert(9999, 'EUR', 'USD');
        $this->assertEqualsWithDelta(9229.077, $result, 0.001);
    }

    //
    // Base Value Conversion
    //

    /**
     * testToBaseValueConvertsFloatToCents — $1.50 should become 150
     */
    public function testToBaseValueConvertsFloatToCents()
    {
        $usd = Currency::findByCode('USD');
        $this->assertEquals(150, $usd->fromFloatValue(1.50));
    }

    /**
     * testFromBaseValueConvertsCentsToFloat — 150 cents should become 1.50
     */
    public function testFromBaseValueConvertsCentsToFloat()
    {
        $usd = Currency::findByCode('USD');
        $this->assertEquals(1.50, $usd->toFloatValue(150));
    }

    /**
     * testBaseValueRoundtrip — converting to base and back should
     * produce the original value
     */
    public function testBaseValueRoundtrip()
    {
        $usd = Currency::findByCode('USD');

        $original = 99.99;
        $base = $usd->fromFloatValue($original);
        $back = $usd->toFloatValue($base);

        $this->assertEquals(9999, $base);
        $this->assertEqualsWithDelta($original, $back, 0.001);
    }

    /**
     * testBaseValueWithZeroDecimals — a currency with 0 decimal scale
     * (like JPY) should treat the value as-is
     */
    public function testBaseValueWithZeroDecimals()
    {
        Model::unguard();
        $jpy = Currency::create([
            'name' => 'Japanese Yen',
            'code' => 'JPY',
            'currency_symbol' => '¥',
            'decimal_point' => '.',
            'decimal_scale' => 0,
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_default' => false,
        ]);
        Model::reguard();

        // 1000 yen → base value is 1000 (no decimal shift)
        $this->assertEquals(1000, $jpy->fromFloatValue(1000));
        $this->assertEquals(1000.0, $jpy->toFloatValue(1000));
    }

    //
    // Formatting
    //

    /**
     * testFormatUsd — $1,234.56 formatted in USD
     */
    public function testFormatUsd()
    {
        $usd = Currency::findByCode('USD');
        $result = $usd->formatCurrency(123456);
        $this->assertEquals('$1,234.56', $result);
    }

    /**
     * testFormatEur — €1.234,56 formatted in EUR (comma decimal, dot thousands)
     */
    public function testFormatEur()
    {
        $eur = Currency::findByCode('EUR');
        $result = $eur->formatCurrency(123456);
        $this->assertEquals('€1.234,56', $result);
    }

    /**
     * testFormatNegativeValue — negative values should include a minus sign
     */
    public function testFormatNegativeValue()
    {
        $usd = Currency::findByCode('USD');
        $result = $usd->formatCurrency(-5000);
        $this->assertEquals('-$50.00', $result);
    }

    /**
     * testFormatWithCustomDecimals — override decimal places
     */
    public function testFormatWithCustomDecimals()
    {
        $usd = Currency::findByCode('USD');
        $result = $usd->formatCurrency(123456, 0);
        $this->assertEquals('$1,235', $result);
    }

    /**
     * testFormatWithoutBaseConversion — when baseValue is false,
     * the number is treated as a float, not cents
     */
    public function testFormatWithoutBaseConversion()
    {
        $usd = Currency::findByCode('USD');
        $result = $usd->formatCurrency(19.99, null, false);
        $this->assertEquals('$19.99', $result);
    }

    //
    // Pair Generation
    //

    /**
     * testGeneratePairsCreatesFromPrimaryToAll — generatePairs should
     * create pairs from the primary currency to all other enabled currencies
     */
    public function testGeneratePairsCreatesFromPrimaryToAll()
    {
        // Seeder creates USD (default), EUR, GBP, AUD
        // generatePairs should create USD→EUR, USD→GBP, USD→AUD
        $count = ExchangeRate::generatePairs();

        $this->assertEquals(3, $count);
        $this->assertNotNull(
            ExchangeRate::where('from_currency_code', 'USD')
                ->where('to_currency_code', 'EUR')
                ->first()
        );
        $this->assertNotNull(
            ExchangeRate::where('from_currency_code', 'USD')
                ->where('to_currency_code', 'GBP')
                ->first()
        );
        $this->assertNotNull(
            ExchangeRate::where('from_currency_code', 'USD')
                ->where('to_currency_code', 'AUD')
                ->first()
        );
    }

    /**
     * testGeneratePairsSkipsExisting — running generatePairs twice
     * should not create duplicate pairs
     */
    public function testGeneratePairsSkipsExisting()
    {
        $count1 = ExchangeRate::generatePairs();
        $count2 = ExchangeRate::generatePairs();

        $this->assertEquals(3, $count1);
        $this->assertEquals(0, $count2);
    }

    /**
     * testGeneratedPairsDefaultToOneToOne — newly generated pairs
     * should have rate_value = 1 (set by beforeSave)
     */
    public function testGeneratedPairsDefaultToOneToOne()
    {
        ExchangeRate::generatePairs();

        $pair = ExchangeRate::where('from_currency_code', 'USD')
            ->where('to_currency_code', 'EUR')
            ->first();

        $this->assertEquals(1, $pair->rate_value);
    }

    /**
     * testPairCodeAttribute — pair_code should return "FROM:TO" format
     */
    public function testPairCodeAttribute()
    {
        $rate = $this->createExchangeRate('USD', 'EUR', 0.92);
        $this->assertEquals('USD:EUR', $rate->pair_code);
    }
}
