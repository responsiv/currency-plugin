<?php namespace Responsiv\Currency\ExchangeTypes;

use Http;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;
use DOMDocument;
use DOMXPath;

class EuropeanCentralBank extends ExchangeBase
{
    const API_URL = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'European Central Bank',
            'description' => 'Free currency exchange rate feed provided by European Central Bank (www.ecb.int).'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        $response = null;
        try {
            $response = Http::get(self::API_URL);
        }
        catch (Exception $ex) { }

        if (!strlen($response)) {
            throw new SystemException('Error loading the European Central Bank feed.');
        }

        $doc = new DOMDocument('1.0');
        $doc->loadXML($response);
        $xPath = new DOMXPath($doc);
        $xPath->registerNamespace('ns', "http://www.ecb.int/vocabulary/2002-08-01/eurofxref");

        if ($fromCurrency == 'EUR') {
            $fromRate = 1;
        }
        else {
            $fromRate = $xPath->query("//gesmes:Envelope/ns:Cube/ns:Cube/ns:Cube[@currency='".$fromCurrency."']");
            if (!$fromRate->length) {
                throw new SystemException(sprintf('Currency rate for "%s" not found.', $fromCurrency));
            }

            $fromRate = $fromRate->item(0)->getAttribute('rate');
        }

        if ($toCurrency == 'EUR') {
            $toRate = 1;
        }
        else {
            $toRate = $xPath->query("//gesmes:Envelope/ns:Cube/ns:Cube/ns:Cube[@currency='".$toCurrency."']");
            if (!$toRate->length) {
                throw new SystemException(sprintf('Currency rate for "%s" not found.', $toCurrency));
            }

            $toRate = $toRate->item(0)->getAttribute('rate');
        }

        if (!strlen($fromRate) || !$fromRate) {
            throw new SystemException(sprintf('Invalid currency rate for "%s".', $fromCurrency));
        }

        if (!strlen($toRate) || !$toRate) {
            throw new SystemException(sprintf('Invalid currency rate for "%s".', $toCurrency));
        }

        return $toRate / $fromRate;
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return [];
    }

}
