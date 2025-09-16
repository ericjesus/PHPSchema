<?php

class validatorText
{
    public static function handle($value, array $rules, string $field): string|bool
    {
        // Base type validation
        switch ($rules['type']) {
            case 'String':
                $result = self::validateString($value);
                break;
            case 'Email':
                $result = self::validateEmail($value);
                break;
            case 'Name':
                $result = self::validadeName($value);
                break;
            case 'Url':
                $result = self::validateUrl($value);
                break;
            case 'Enum':
                $result = self::validateEnum($value, $rules['options']);
                break;
            default:
                return true;
        }

        if ($result !== true) {
            return $result;
        }

        // Additional validations for text types
        if ($rules['type'] !== 'Enum') {
            return self::validateLength(
                $value,
                $rules['min_length'] ?? null,
                $rules['max_length'] ?? null
            );
        }

        return true;
    }

    private static function validateString($value): string|bool
    {
        if (!is_string($value)) {
            return "Must be a string.";
        }

        return true;
    }

    private static function validateEmail($value): string|bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "Must be a valid email.";
        }

        return true;
    }

    private static function validateUrl($value): string|bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return "Invalid URL format.";
        }

        return true;
    }

    private static function validadeName($value): string|bool
    {
        if (!is_string($value)) {
            return "Must be a valid name.";
        }

        if (!preg_match("/^[a-zA-ZÀ-ÖØ-öø-ÿ\s]+$/", $value)) {
            return "Name can only contain letters and spaces.";
        }

        $nameParts = explode(' ', $value);
        if (count($nameParts) < 2 || empty($nameParts[1])) {
            return "Name must include at least a first name and a surname.";
        }

        return true;
    }

    private static function validateEnum($value, array $options): string|bool
    {
        if (!in_array($value, $options)) {
            return "Invalid option, '$value' is invalid.";
        }

        return true;
    }

    private static function validateLength($value, ?int $minLength = null, ?int $maxLength = null): string|bool
    {
        if ($minLength !== null && strlen($value) < $minLength) {
            return "Must exceed {$minLength} characters.";
        }

        if ($maxLength !== null && strlen($value) > $maxLength) {
            return "Must not exceed {$maxLength} characters.";
        }

        return true;
    }
}
