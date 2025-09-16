<?php

class validatorNumber
{
    public static function handle($value, array $rules, string $field): string|bool
    {
        // Base type validation
        switch ($rules['type']) {
            case 'Int':
                $result = self::validateInteger($value);
                break;
            case 'Decimal':
                $result = self::validateDecimal($value);
                break;
            case 'Bool':
                $result = self::validateBoolean($value);
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
                $rules['min_value'] ?? null,
                $rules['max_value'] ?? null
            );
            if ($rangeResult !== true) {
                return $rangeResult;
            }

            // Number length validation
            return self::validateIntegerLength(
                $value,
                $rules['min_length'] ?? null,
                $rules['max_length'] ?? null
            );
        }

        return true;
    }

    private static function validateInteger($value): string|bool
    {
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            return "Must be a valid integer.";
        }

        return true;
    }

    private static function validateDecimal($value): string|bool
    {
        if (!is_numeric($value) || !preg_match('/^-?\d+(\.\d+)?$/', (string)$value)) {
            return "Must be a valid decimal number.";
        }

        return true;
    }

    private static function validateRange($value, ?int $minValue = null, ?int $maxValue = null): string|bool
    {
        if ($minValue !== null && $value < $minValue) {
            return "Must be at least {$minValue}.";
        }

        if ($maxValue !== null && $value > $maxValue) {
            return "Must not exceed {$maxValue}.";
        }

        return true;
    }

    private static function validateBoolean($value): string|bool
    {
        if (!is_bool($value) && !in_array($value, [0, 1, "0", "1", "true", "false"], true)) {
            return "Must be a valid boolean (true/false or 0/1).";
        }

        return true;
    }

    private static function validateIntegerLength($value, ?int $minLength = null, ?int $maxLength = null): string|bool
    {
        $valueStr = (string) $value;
        
        if ($minLength !== null && strlen($valueStr) < $minLength) {
            return "Must exceed {$minLength} characters.";
        }

        if ($maxLength !== null && strlen($valueStr) > $maxLength) {
            return "Must not exceed {$maxLength} characters.";
        }

        return true;
    }
}
