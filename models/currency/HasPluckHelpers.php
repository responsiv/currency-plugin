<?php namespace Responsiv\Currency\Models\Currency;

use Cache;

/**
 * HasPluckHelpers
 */
trait HasPluckHelpers
{
    /**
     * @var array nameList cache for nameList() method
     */
    protected static $nameList = null;

    /**
     * @var array codeList is a cache of enabled currencies.
     */
    protected static $codeList;

    /**
     * @var array codeListAll is a cache of available currencies.
     */
    protected static $codeListAll;

    /**
     * getNameList
     */
    public static function getNameList()
    {
        if (self::$nameList) {
            return self::$nameList;
        }

        return self::$nameList = static::applyEnabled()->lists('name', 'id');
    }

    /**
     * getCodeList currencies, used on the front-end.
     */
    public static function getCodeList(): array
    {
        if (self::$codeList) {
            return self::$codeList;
        }

        $isEnabled = Cache::remember('responsiv.currency.currencies', 1440, function() {
            return static::applyEnabled()->lists('name', 'code');
        });

        return self::$codeList = $isEnabled;
    }

    /**
     * getCodeList currencies, used on the back-end.
     */
    public static function getAllCodeList(): array
    {
        if (self::$codeListAll) {
            return self::$codeListAll;
        }

        return self::$codeListAll = static::lists('name', 'code');
    }
}
