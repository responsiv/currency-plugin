<?php namespace Responsiv\Currency\Tests;

use Site;
use Currency as CurrencyManager;
use PluginTestCase;
use Responsiv\Currency\Models\Currency;
use System\Models\SiteDefinition;
use System\Models\SiteGroup;
use October\Rain\Database\Model;

/**
 * SiteCurrencyTest validates how the active and hard currencies of a site fall
 * back through the site currency, the site group base currency and the default.
 */
class SiteCurrencyTest extends PluginTestCase
{
    /**
     * @var bool useTransactions isolates each test with a database transaction
     */
    protected $useTransactions = true;

    /**
     * setUp
     */
    public function setUp(): void
    {
        parent::setUp();

        Currency::clearCache();
        Currency::clearDefaultableCache();
    }

    /**
     * createSite creates a site in a new group, with optional site and base currencies
     */
    protected function createSite(?string $siteCode = null, ?string $baseCode = null): SiteDefinition
    {
        Model::unguard();
        $group = SiteGroup::create([
            'name' => 'Regional',
            'code' => 'regional',
        ]);

        if ($baseCode) {
            $group->base_currency_id = Currency::findByCode($baseCode)->id;
            $group->save();
        }

        $site = SiteDefinition::create([
            'name' => 'Regional',
            'code' => 'regional',
            'is_enabled' => true,
            'is_enabled_edit' => true,
            'group_id' => $group->id,
        ]);

        if ($siteCode) {
            $site->currency_id = Currency::findByCode($siteCode)->id;
            $site->save();
        }
        Model::reguard();

        Site::resetCache();

        return $site->fresh();
    }

    /**
     * testSiteCurrencyIsActive verifies a site currency wins over the base currency
     */
    public function testSiteCurrencyIsActive()
    {
        $site = $this->createSite('AUD', 'EUR');

        Site::withContext($site->id, function() use ($site) {
            $this->assertEquals('AUD', CurrencyManager::getActiveCode());
            $this->assertEquals('EUR', CurrencyManager::getPrimaryCode());
            $this->assertEquals('AUD', $site->hard_currency->code);
            $this->assertEquals('AUD', $site->hard_currency_code);
            $this->assertEquals('EUR', $site->base_currency->code);
            $this->assertEquals('EUR', $site->base_currency_code);
        });
    }

    /**
     * testActiveCurrencyFallsBackToBaseCurrency verifies a site without a currency
     * displays the site group base currency
     */
    public function testActiveCurrencyFallsBackToBaseCurrency()
    {
        $site = $this->createSite(null, 'EUR');

        Site::withContext($site->id, function() use ($site) {
            $this->assertEquals('EUR', CurrencyManager::getActiveCode());
            $this->assertEquals('EUR', CurrencyManager::getPrimaryCode());
            $this->assertEquals('EUR', $site->hard_currency->code);
            $this->assertEquals('EUR', $site->hard_currency_code);
            $this->assertNull($site->currency_code);
        });
    }

    /**
     * testActiveCurrencyFallsBackToDefault verifies a site without a currency in a
     * group without a base currency displays the default currency
     */
    public function testActiveCurrencyFallsBackToDefault()
    {
        $site = $this->createSite();
        $defaultCode = Currency::getDefaultCode();

        Site::withContext($site->id, function() use ($site, $defaultCode) {
            $this->assertEquals($defaultCode, CurrencyManager::getActiveCode());
            $this->assertEquals($defaultCode, CurrencyManager::getPrimaryCode());
            $this->assertEquals($defaultCode, $site->hard_currency->code);
            $this->assertEquals($defaultCode, $site->hard_currency_code);
            $this->assertEquals($defaultCode, $site->base_currency_code);

            // The site group attribute is the raw setting, empty when unset
            $this->assertNull($site->group->base_currency_code);
        });
    }
}
