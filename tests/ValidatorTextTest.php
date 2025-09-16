<?php

require_once __DIR__ . '/../src/core/validators/validator.text.php';

class ValidatorTextTest
{
    private function getTranslator(): callable
    {
        return function(string $category, string $key, array $placeholders = []) {
            // Simple translator for testing
            $messages = [
                'text' => [
                    'string' => 'Must be a valid string.',
                    'email' => 'Must be a valid email address.',
                    'url' => 'Invalid URL format.',
                    'name' => 'Must be a valid name.',
                    'name_letters_only' => 'Name can only contain letters and spaces.',
                    'name_full_required' => 'Name must include at least a first and last name.',
                    'enum_invalid' => "Invalid option, '{value}' is invalid.",
                    'min_length' => 'Must be at least {min} characters long.',
                    'max_length' => 'Must not exceed {max} characters.',
                    'phone_format' => 'Must be a valid string for phone.',
                    'phone_invalid' => 'Invalid phone format.',
                    'cpf_format' => 'Must be a valid string for CPF.',
                    'cpf_invalid' => 'Invalid CPF.',
                    'cnpj_format' => 'Must be a valid string for CNPJ.',
                    'cnpj_invalid' => 'Invalid CNPJ.',
                    'uuid_format' => 'Must be a valid string for UUID.',
                    'uuid_invalid' => 'Invalid UUID format.'
                ]
            ];

            $message = $messages[$category][$key] ?? "Missing translation: {$category}.{$key}";
            
            foreach ($placeholders as $placeholder => $value) {
                $message = str_replace("{{$placeholder}}", $value, $message);
            }
            
            return $message;
        };
    }

    public function testStringValidation()
    {
        $translator = $this->getTranslator();

        // Valid strings
        $result = validatorText::handle('Hello World', ['type' => 'String'], $translator);
        assertTrue($result === true, 'Valid string should pass');

        // Invalid non-string
        $result = validatorText::handle(123, ['type' => 'String'], $translator);
        assertEquals('Must be a valid string.', $result, 'Non-string should fail');
    }

    public function testEmailValidation()
    {
        $translator = $this->getTranslator();

        // Valid emails
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'test+tag@gmail.com'
        ];

        foreach ($validEmails as $email) {
            $result = validatorText::handle($email, ['type' => 'Email'], $translator);
            assertTrue($result === true, "Email '{$email}' should be valid");
        }

        // Invalid emails
        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'test@',
            'test@domain',
            ''
        ];

        foreach ($invalidEmails as $email) {
            $result = validatorText::handle($email, ['type' => 'Email'], $translator);
            assertEquals('Must be a valid email address.', $result, "Email '{$email}' should be invalid");
        }
    }

    public function testUrlValidation()
    {
        $translator = $this->getTranslator();

        // Valid URLs
        $validUrls = [
            'https://example.com',
            'http://localhost:8080',
            'ftp://files.example.com',
            'https://subdomain.example.com/path?query=value'
        ];

        foreach ($validUrls as $url) {
            $result = validatorText::handle($url, ['type' => 'Url'], $translator);
            assertTrue($result === true, "URL '{$url}' should be valid");
        }

        // Invalid URLs
        $invalidUrls = [
            'invalid-url',
            'example.com',
            ''
        ];

        foreach ($invalidUrls as $url) {
            $result = validatorText::handle($url, ['type' => 'Url'], $translator);
            assertEquals('Invalid URL format.', $result, "URL '{$url}' should be invalid");
        }
    }

    public function testNameValidation()
    {
        $translator = $this->getTranslator();

        // Valid names (without hyphens - regex only accepts letters and spaces)
        $validNames = [
            'John Doe',
            'María García',
            'José da Silva'
        ];

        foreach ($validNames as $name) {
            $result = validatorText::handle($name, ['type' => 'Name'], $translator);
            assertTrue($result === true, "Name '{$name}' should be valid");
        }

        // Invalid names (single name)
        $result = validatorText::handle('John', ['type' => 'Name'], $translator);
        assertEquals('Name must include at least a first and last name.', $result);

        // Invalid names (with numbers)
        $result = validatorText::handle('John123 Doe', ['type' => 'Name'], $translator);
        assertEquals('Name can only contain letters and spaces.', $result);
    }

    public function testEnumValidation()
    {
        $translator = $this->getTranslator();

        // Valid enum values
        $rules = ['type' => 'Enum', 'options' => ['active', 'inactive', 'pending']];
        
        foreach ($rules['options'] as $option) {
            $result = validatorText::handle($option, $rules, $translator);
            assertTrue($result === true, "Enum value '{$option}' should be valid");
        }

        // Invalid enum value
        $result = validatorText::handle('invalid', $rules, $translator);
        assertEquals("Invalid option, 'invalid' is invalid.", $result);
    }

    public function testPhoneValidation()
    {
        $translator = $this->getTranslator();

        // Valid phone numbers
        $validPhones = [
            '11987654321',
            '5511987654321',
            '1134567890',
            '551134567890'
        ];

        foreach ($validPhones as $phone) {
            $result = validatorText::handle($phone, ['type' => 'Phone'], $translator);
            assertTrue($result === true, "Phone '{$phone}' should be valid");
        }

        // Invalid phone numbers
        $invalidPhones = [
            'invalid-phone',
            '123456',
            '00987654321',
            ''
        ];

        foreach ($invalidPhones as $phone) {
            $result = validatorText::handle($phone, ['type' => 'Phone'], $translator);
            assertEquals('Invalid phone format.', $result, "Phone '{$phone}' should be invalid");
        }
    }

    public function testCpfValidation()
    {
        $translator = $this->getTranslator();

        // Valid CPF (using a valid algorithm-generated CPF)
        $validCpf = '123.456.789-09';
        $result = validatorText::handle($validCpf, ['type' => 'CPF'], $translator);
        assertTrue($result === true, "CPF '{$validCpf}' should be valid");

        // Invalid CPFs
        $invalidCpfs = [
            '123.456.789-00',
            '111.111.111-11',
            '12345678901',
            'invalid-cpf',
            ''
        ];

        foreach ($invalidCpfs as $cpf) {
            $result = validatorText::handle($cpf, ['type' => 'CPF'], $translator);
            assertEquals('Invalid CPF.', $result, "CPF '{$cpf}' should be invalid");
        }
    }

    public function testCnpjValidation()
    {
        $translator = $this->getTranslator();

        // Valid CNPJ (using a valid algorithm-generated CNPJ)
        $validCnpj = '11.222.333/0001-81';
        $result = validatorText::handle($validCnpj, ['type' => 'CNPJ'], $translator);
        assertTrue($result === true, "CNPJ '{$validCnpj}' should be valid");

        // Invalid CNPJs
        $invalidCnpjs = [
            '11.222.333/0001-00',
            '11.111.111/1111-11',
            '1122233300018',
            'invalid-cnpj',
            ''
        ];

        foreach ($invalidCnpjs as $cnpj) {
            $result = validatorText::handle($cnpj, ['type' => 'CNPJ'], $translator);
            assertEquals('Invalid CNPJ.', $result, "CNPJ '{$cnpj}' should be invalid");
        }
    }

    public function testUuidValidation()
    {
        $translator = $this->getTranslator();

        // Valid UUIDs (v4 format only: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx)
        $validUuids = [
            '550e8400-e29b-41d4-a716-446655440000',
            'f47ac10b-58cc-4372-a567-0e02b2c3d479'
        ];

        foreach ($validUuids as $uuid) {
            $result = validatorText::handle($uuid, ['type' => 'UUID'], $translator);
            assertTrue($result === true, "UUID '{$uuid}' should be valid");
        }

        // Invalid UUIDs
        $invalidUuids = [
            'invalid-uuid',
            '550e8400-e29b-41d4-a716',
            '550e8400-e29b-31d4-a716-446655440000', // wrong version (3, not 4)
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8', // UUID v1, not v4
            '',
            '550e8400e29b41d4a716446655440000' // no hyphens
        ];

        foreach ($invalidUuids as $uuid) {
            $result = validatorText::handle($uuid, ['type' => 'UUID'], $translator);
            assertEquals('Invalid UUID format.', $result, "UUID '{$uuid}' should be invalid");
        }
    }

    public function testMinMaxLengthValidation()
    {
        $translator = $this->getTranslator();

        // Test min_length
        $rules = ['type' => 'String', 'min_length' => 5];
        $result = validatorText::handle('test', $rules, $translator);
        assertEquals('Must be at least 5 characters long.', $result);

        $result = validatorText::handle('testing', $rules, $translator);
        assertTrue($result === true);

        // Test max_length
        $rules = ['type' => 'String', 'max_length' => 5];
        $result = validatorText::handle('testing', $rules, $translator);
        assertEquals('Must not exceed 5 characters.', $result);

        $result = validatorText::handle('test', $rules, $translator);
        assertTrue($result === true);
    }
}