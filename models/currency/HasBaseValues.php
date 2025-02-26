<?php namespace Responsiv\Currency\Models\Currency;

/**
 * HasBaseValues introduces methods for working with base values and formatting
 */
trait HasBaseValues
{
    /**
     * toBaseValue converts a float to a base value stored in the database,
     * a base value has no decimal point. E.g. converts 1.00 to 100
     */
    public function toBaseValue($value)
    {
        $result = str_replace(
            $this->thousand_separator,
            '',
            (string) $value
        );

        $result = str_replace(
            $this->decimal_point,
            '.',
            $value
        );

        return $this->fromFloatValue(floatval($result));
    }

    /**
     * fromBaseValue converts from a base value to a float value from the database,
     * the returning value introduces a decimal point. E.g. converts 100 to 1.00
     */
    public function fromBaseValue($value)
    {
        $result = $this->toFloatValue(intval($value));

        return number_format(
            $result,
            $this->decimal_scale,
            $this->decimal_point,
            ""
        );
    }

    /**
     * fromFloatValue will return an integer representation without formatting
     */
    public function fromFloatValue(float $value): int
    {
        return $value * pow(10, (int) $this->decimal_scale);
    }

    /**
     * toFloatValue will return a float representation without formatting
     */
    public function toFloatValue(int $value): float
    {
        return $value / pow(10, (int) $this->decimal_scale);
    }

    /**
     * @deprecated use fromFloatValue
     */
    public function toBaseValueRaw(float $value): int
    {
        return $this->toFloatValue($value);
    }

    /**
     * @deprecated use toFloatValue
     */
    public function fromBaseValueRaw(int $value): float
    {
        return $this->fromFloatValue($value);
    }
}
