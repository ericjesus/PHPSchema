<?php

class validatorNumber
{
    public static function handle($value, array $rules, callable $translator): string|bool
    {
        // Base type validation
        switch ($rules['type']) {
            case 'Int':
                $result = self::validateInteger($value, $translator);
                break;
            case 'Decimal':
                $result = self::validateDecimal($value, $translator);
                break;
            case 'Bool':
                $result = self::validateBoolean($value, $translator);
                break;
            default:
                return true;
        }

        if ($result !== true) {
            return $result;
        }

        // Additional validations for numbers
        if ($rules['type'] == 'Int') {
            // Range validation
            $rangeResult = self::validateRange(
                $value,
                $translator,
                $rules['min_value'] ?? null,
                $rules['max_value'] ?? null
            );
            if ($rangeResult !== true) {
                return $rangeResult;
            }

            // Number length validation
            return self::validateIntegerLength(
                $value,
                $translator,
                $rules['min_length'] ?? null,
                $rules['max_length'] ?? null
            );
        }

        return true;
    }

    private static function validateInteger($value, callable $translator): string|bool
    {
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            return $translator('number', 'integer');
        }

        return true;
    }

    private static function validateDecimal($value, callable $translator): string|bool
    {
        if (!is_numeric($value) || !preg_match('/^-?\d+(\.\d+)?$/', (string)$value)) {
            return $translator('number', 'decimal');
        }

        return true;
    }

    private static function validateRange($value, callable $translator, ?int $minValue = null, ?int $maxValue = null): string|bool
    {
        if ($minValue !== null && $value < $minValue) {
            return $translator('number', 'min_value', ['min' => $minValue]);
        }

        if ($maxValue !== null && $value > $maxValue) {
            return $translator('number', 'max_value', ['max' => $maxValue]);
        }

        return true;
    }

    private static function validateBoolean($value, callable $translator): string|bool
    {
        if (!is_bool($value) && !in_array($value, [0, 1, "0", "1", "true", "false"], true)) {
            return $translator('number', 'boolean');
        }

        return true;
    }

    private static function validateIntegerLength($value, callable $translator, ?int $minLength = null, ?int $maxLength = null): string|bool
    {
        $valueStr = (string) $value;
        
        if ($minLength !== null && strlen($valueStr) < $minLength) {
            return $translator('number', 'min_length', ['min' => $minLength]);
        }

        if ($maxLength !== null && strlen($valueStr) > $maxLength) {
            return $translator('number', 'max_length', ['max' => $maxLength]);
        }

        return true;
    }
}
