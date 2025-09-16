<?php

class validatorText
{
    public static function handle($value, array $rules, callable $translator): string|bool
    {
        // Base type validation
        switch ($rules['type']) {
            case 'String':
                $result = self::validateString($value, $translator);
                break;
            case 'Email':
                $result = self::validateEmail($value, $translator);
                break;
            case 'Name':
                $result = self::validadeName($value, $translator);
                break;
            case 'Url':
                $result = self::validateUrl($value, $translator);
                break;
            case 'Enum':
                $result = self::validateEnum($value, $rules['options'], $translator);
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
                $translator,
                $rules['min_length'] ?? null,
                $rules['max_length'] ?? null
            );
        }

        return true;
    }

    private static function validateString($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'string');
        }

        return true;
    }

    private static function validateEmail($value, callable $translator): string|bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $translator('text', 'email');
        }

        return true;
    }

    private static function validateUrl($value, callable $translator): string|bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return $translator('text', 'url');
        }

        return true;
    }

    private static function validadeName($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'name');
        }

        if (!preg_match("/^[a-zA-ZÀ-ÖØ-öø-ÿ\s]+$/", $value)) {
            return $translator('text', 'name_letters_only');
        }

        $nameParts = explode(' ', $value);
        if (count($nameParts) < 2 || empty($nameParts[1])) {
            return $translator('text', 'name_full_required');
        }

        return true;
    }

    private static function validateEnum($value, array $options, callable $translator): string|bool
    {
        if (!in_array($value, $options)) {
            return $translator('text', 'enum_invalid', ['value' => $value]);
        }

        return true;
    }

    private static function validateLength($value, callable $translator, ?int $minLength = null, ?int $maxLength = null): string|bool
    {
        if ($minLength !== null && strlen($value) < $minLength) {
            return $translator('text', 'min_length', ['min' => $minLength]);
        }

        if ($maxLength !== null && strlen($value) > $maxLength) {
            return $translator('text', 'max_length', ['max' => $maxLength]);
        }

        return true;
    }
}
