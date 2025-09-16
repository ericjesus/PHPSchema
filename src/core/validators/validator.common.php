<?php

class validatorCommon
{
    public static function validateRequired($value, bool $required, callable $translator): string|bool
    {
        if (!isset($value) && $required) {
            return $translator('common', 'required');
        }

        return true;
    }

    public static function validateNotEmpty($value, bool $notEmpty, callable $translator): string|bool
    {
        if ($notEmpty && empty($value) && $value != 0) {
            return $translator('common', 'not_empty');
        }

        return true;
    }

    public static function validateStrictMode(array $data, array $schema, bool $strictMode, callable $translator): array
    {
        $errors = [];
        
        if ($strictMode) {
            $extraFields = array_diff(array_keys($data), array_keys($schema));
            if (!empty($extraFields)) {
                foreach ($extraFields as $extraField) {
                    $errors[$extraField] = $translator('common', 'strict_mode');
                }
            }
        }
        
        return $errors;
    }
}