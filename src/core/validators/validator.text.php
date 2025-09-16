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
            case 'Phone':
                $result = self::validatePhone($value, $translator);
                break;
            case 'CPF':
                $result = self::validateCPF($value, $translator);
                break;
            case 'CNPJ':
                $result = self::validateCNPJ($value, $translator);
                break;
            case 'UUID':
                $result = self::validateUUID($value, $translator);
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

    private static function validatePhone($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'phone_format');
        }

        // Remove all non-numeric characters for validation
        $cleanPhone = preg_replace('/[^0-9]/', '', $value);

        // Brazilian phone validation (with or without country code)
        // Mobile: 11 digits with country code (5511987654321) or 11 digits (11987654321)
        // Landline: 10 digits with country code (5511987654321) or 10 digits (1134567890)
        if (!preg_match('/^(?:55)?(?:1[1-9]|2[12478]|3[1234578]|4[13578]|5[13578]|6[1235678]|7[134579]|8[1345678]|9[1-9])[0-9]{8,9}$/', $cleanPhone)) {
            return $translator('text', 'phone_invalid');
        }

        return true;
    }

    private static function validateCPF($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'cpf_format');
        }

        // Remove formatting
        $cpf = preg_replace('/[^0-9]/', '', $value);

        // Check length
        if (strlen($cpf) !== 11) {
            return $translator('text', 'cpf_invalid');
        }

        // Check for known invalid sequences
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return $translator('text', 'cpf_invalid');
        }

        // Validate checksum
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $digit1 = ($sum * 10) % 11;
        if ($digit1 == 10) $digit1 = 0;

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $digit2 = ($sum * 10) % 11;
        if ($digit2 == 10) $digit2 = 0;

        if (intval($cpf[9]) !== $digit1 || intval($cpf[10]) !== $digit2) {
            return $translator('text', 'cpf_invalid');
        }

        return true;
    }

    private static function validateCNPJ($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'cnpj_format');
        }

        // Remove formatting
        $cnpj = preg_replace('/[^0-9]/', '', $value);

        // Check length
        if (strlen($cnpj) !== 14) {
            return $translator('text', 'cnpj_invalid');
        }

        // Check for known invalid sequences
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return $translator('text', 'cnpj_invalid');
        }

        // Validate first check digit
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights1[$i];
        }
        $digit1 = $sum % 11;
        $digit1 = $digit1 < 2 ? 0 : 11 - $digit1;

        // Validate second check digit
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights2[$i];
        }
        $digit2 = $sum % 11;
        $digit2 = $digit2 < 2 ? 0 : 11 - $digit2;

        if (intval($cnpj[12]) !== $digit1 || intval($cnpj[13]) !== $digit2) {
            return $translator('text', 'cnpj_invalid');
        }

        return true;
    }

    private static function validateUUID($value, callable $translator): string|bool
    {
        if (!is_string($value)) {
            return $translator('text', 'uuid_format');
        }

        // UUID v4 pattern: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
        // where y is 8, 9, A, or B
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $value)) {
            return $translator('text', 'uuid_invalid');
        }

        return true;
    }
}
