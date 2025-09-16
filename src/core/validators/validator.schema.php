<?php

class validatorSchema
{
    public static function handle($value, array $rules, string $field, callable $validateCallback, bool $strictMode = true): string|array|bool
    {
        // Decode JSON only if not empty
        $valueDecode = ($value !== '') ? json_decode(html_entity_decode($value), true) : $value;

        // 1️⃣ Array of schemas
        if (isset($rules['type'][0]) && is_array($rules['type'][0])) {
            return self::validateArraySchema($valueDecode, $rules, $validateCallback, $strictMode);
        }

        // 2️⃣ Single sub-schema (associative array)
        return self::validateObjectSchema($valueDecode, $rules, $validateCallback, $strictMode);
    }

    private static function validateArraySchema($valueDecode, array $rules, callable $validateCallback, bool $strictMode): string|array|bool
    {
        $subSchema = $rules['type'][0];

        // If empty and not_empty=false, skip
        if ($valueDecode === '' && (!isset($rules['not_empty']) || $rules['not_empty'] === false)) {
            return true;
        }

        if (!is_array($valueDecode) || array_keys($valueDecode) !== range(0, count($valueDecode) - 1)) {
            return "Must be an array of items.";
        }

        $errors = [];
        foreach ($valueDecode as $idx => $item) {
            $res = $validateCallback($item, $subSchema, $strictMode);
            if ($res !== true) {
                $errors[$idx] = $res['error'];
            }
        }

        return count($errors) > 0 ? $errors : true;
    }

    private static function validateObjectSchema($valueDecode, array $rules, callable $validateCallback, bool $strictMode): string|array|bool
    {
        if ($valueDecode === '' && (!isset($rules['not_empty']) || $rules['not_empty'] === false)) {
            return true;
        }

        if (!is_array($valueDecode)) {
            return "Must be an object matching the schema.";
        }

        $res = $validateCallback($valueDecode, $rules['type'], $strictMode);
        return $res !== true ? $res['error'] : true;
    }
}