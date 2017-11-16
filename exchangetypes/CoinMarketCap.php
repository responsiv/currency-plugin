<?php namespace Responsiv\Currency\ExchangeTypes;

use Http;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;

class CoinMarketCap extends ExchangeBase
{
    const API_URL = 'https://api.coinmarketcap.com/v1/ticker/%s/?convert=%s';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'CoinMarketCap',
            'description' => 'Exchange rate service for cryptocurrencies'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        $fromCrypto = $this->getCryptoNameFromCode($fromCurrency);
        $toCrypto = $this->getCryptoNameFromCode($toCurrency);

        /*
         * If both are detected as crypto, treat the toCurrency as fiat
         */
        if ($fromCrypto && $toCrypto) {
            $toCrypto = null;
        }
        elseif (!$fromCrypto && !$toCrypto) {
            throw new SystemException('A valid cryptocurrency could not be found.');
        }

        $cryptoName = $fromCrypto ?: $toCrypto;
        $fiatCode = $fromCrypto ? $toCurrency : $fromCurrency;

        $response = null;
        try {
            $response = Http::get(sprintf(self::API_URL, $cryptoName, $fiatCode));
            $body = (string) $response;
        }
        catch (Exception $ex) { }

        if (!strlen($body)) {
            throw new SystemException('Error loading the CoinMarketCap currency exchange feed.');
        }

        $result = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SystemException('The CoinMarketCap currency exchange rate service returned invalid data.');
        }

        if (!array_key_exists(0, $result)) {
            throw new SystemException('The CoinMarketCap currency exchange rate service returned invalid data.');
        }

        $detail = $result[0];

        if (!$price = array_get($detail, 'price_'.strtolower($fiatCode))) {
            throw new SystemException('The CoinMarketCap currency exchange rate service is missing the destination currency.');
        }

        if ($fromCrypto) {
            $rate = $price * 1;
        }
        else {
            $rate = 1 / $price;
        }

        return $rate;
    }

    protected function getCryptoNameFromCode($code)
    {
        $map = $this->getCryptoCodeMap();

        return array_get($map, $code);
    }

    protected function getCryptoCodeMap()
    {
        return [
            'BCH' => 'bitcoin-cash',
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'XRP' => 'ripple',
            'LTC' => 'litecoin',
            'DASH' => 'dash',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return [];
    }
}
