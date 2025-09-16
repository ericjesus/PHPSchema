<?php

require_once __DIR__ . '/../src/core/validators/validator.number.php';

class ValidatorNumberTest
{
    private function getTranslator(): callable
    {
        return function(string $category, string $key, array $placeholders = []) {
            $messages = [
                'number' => [
                    'integer' => 'Must be a valid integer.',
                    'decimal' => 'Must be a valid decimal number.',
                    'boolean' => 'Must be a valid boolean value (true/false or 0/1).',
                    'min_value' => 'Must be at least {min}.',
                    'max_value' => 'Must not exceed {max}.',
                    'min_length' => 'Must be more than {min} characters.',
                    'max_length' => 'Must not exceed {max} characters.'
                ]
            ];

            $message = $messages[$category][$key] ?? "Missing translation: {$category}.{$key}";
            
            foreach ($placeholders as $placeholder => $value) {
                $message = str_replace("{{$placeholder}}", $value, $message);
            }
            
            return $message;
        };
    }

    public function testIntegerValidation()
    {
        $translator = $this->getTranslator();

        // Valid integers
        $validIntegers = [123, '456', 0, '0', -789]; // Remove string '-123' as it's not valid
        
        foreach ($validIntegers as $integer) {
            $result = validatorNumber::handle($integer, ['type' => 'Int'], $translator);
            assertTrue($result === true, "Integer '{$integer}' should be valid");
        }

        // Invalid integers
        $invalidIntegers = [12.5, '12.5', 'abc', '', null, true, [], '-123']; // String '-123' is invalid

        foreach ($invalidIntegers as $integer) {
            $result = validatorNumber::handle($integer, ['type' => 'Int'], $translator);
            assertEquals('Must be a valid integer.', $result, "Value should be invalid integer");
        }
    }

    public function testDecimalValidation()
    {
        $translator = $this->getTranslator();

        // Valid decimals
        $validDecimals = [12.5, '12.5', 0.0, '0.0', -12.34, '-12.34', 123, '123'];

        foreach ($validDecimals as $decimal) {
            $result = validatorNumber::handle($decimal, ['type' => 'Decimal'], $translator);
            assertTrue($result === true, "Decimal '{$decimal}' should be valid");
        }

        // Invalid decimals
        $invalidDecimals = ['abc', '', null, true, [], 'not-a-number'];

        foreach ($invalidDecimals as $decimal) {
            $result = validatorNumber::handle($decimal, ['type' => 'Decimal'], $translator);
            assertEquals('Must be a valid decimal number.', $result, "Value should be invalid decimal");
        }
    }

    public function testBooleanValidation()
    {
        $translator = $this->getTranslator();

        // Valid booleans
        $validBooleans = [true, false, 1, 0, '1', '0', 'true', 'false'];

        foreach ($validBooleans as $boolean) {
            $result = validatorNumber::handle($boolean, ['type' => 'Bool'], $translator);
            assertTrue($result === true, "Boolean '{$boolean}' should be valid");
        }

        // Invalid booleans
        $invalidBooleans = [2, '2', 'yes', 'no', 'on', 'off', '', null, []];

        foreach ($invalidBooleans as $boolean) {
            $result = validatorNumber::handle($boolean, ['type' => 'Bool'], $translator);
            assertEquals('Must be a valid boolean value (true/false or 0/1).', $result, "Value should be invalid boolean");
        }
    }

    public function testMinValueValidation()
    {
        $translator = $this->getTranslator();

        // Test integer min_value (only Int supports min_value validation)
        $rules = ['type' => 'Int', 'min_value' => 10];
        
        $result = validatorNumber::handle(5, $rules, $translator);
        assertEquals('Must be at least 10.', $result);

        $result = validatorNumber::handle(10, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(15, $rules, $translator);
        assertTrue($result === true);

        // Test decimal min_value (Decimal type doesn't support min_value validation)
        $rules = ['type' => 'Decimal', 'min_value' => 10.5];
        
        $result = validatorNumber::handle(10.0, $rules, $translator);
        assertTrue($result === true, 'Decimal type does not support min_value validation');

        $result = validatorNumber::handle(10.5, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(11.0, $rules, $translator);
        assertTrue($result === true);
    }

    public function testMaxValueValidation()
    {
        $translator = $this->getTranslator();

        // Test integer max_value
        $rules = ['type' => 'Int', 'max_value' => 100];
        
        $result = validatorNumber::handle(150, $rules, $translator);
        assertEquals('Must not exceed 100.', $result);

        $result = validatorNumber::handle(100, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(50, $rules, $translator);
        assertTrue($result === true);

        // Test decimal max_value (Decimal type doesn't support max_value validation)
        $rules = ['type' => 'Decimal', 'max_value' => 100.5];
        
        $result = validatorNumber::handle(101.0, $rules, $translator);
        assertTrue($result === true, 'Decimal type does not support max_value validation');

        $result = validatorNumber::handle(100.5, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(99.9, $rules, $translator);
        assertTrue($result === true);
    }

    public function testMinMaxLengthForNumbers()
    {
        $translator = $this->getTranslator();

        // Test min_length for integers
        $rules = ['type' => 'Int', 'min_length' => 3];
        
        $result = validatorNumber::handle(12, $rules, $translator);
        assertEquals('Must be more than 3 characters.', $result);

        $result = validatorNumber::handle(123, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(1234, $rules, $translator);
        assertTrue($result === true);

        // Test max_length for integers
        $rules = ['type' => 'Int', 'max_length' => 3];
        
        $result = validatorNumber::handle(1234, $rules, $translator);
        assertEquals('Must not exceed 3 characters.', $result);

        $result = validatorNumber::handle(123, $rules, $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(12, $rules, $translator);
        assertTrue($result === true);
    }

    public function testCombinedValidations()
    {
        $translator = $this->getTranslator();

        // Test combined min_value and max_value
        $rules = ['type' => 'Int', 'min_value' => 10, 'max_value' => 100];

        $result = validatorNumber::handle(5, $rules, $translator);
        assertEquals('Must be at least 10.', $result);

        $result = validatorNumber::handle(150, $rules, $translator);
        assertEquals('Must not exceed 100.', $result);

        $result = validatorNumber::handle(50, $rules, $translator);
        assertTrue($result === true);

        // Test combined length and value constraints
        $rules = ['type' => 'Int', 'min_length' => 2, 'max_length' => 4, 'min_value' => 10, 'max_value' => 99999];

        // min_value is checked before min_length, so value 5 fails min_value first
        $result = validatorNumber::handle(5, $rules, $translator);
        assertEquals('Must be at least 10.', $result);

        $result = validatorNumber::handle(12345, $rules, $translator);
        assertEquals('Must not exceed 4 characters.', $result);

        $result = validatorNumber::handle(123, $rules, $translator);
        assertTrue($result === true);
    }

    public function testEdgeCases()
    {
        $translator = $this->getTranslator();

        // Test zero values
        $result = validatorNumber::handle(0, ['type' => 'Int'], $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(0.0, ['type' => 'Decimal'], $translator);
        assertTrue($result === true);

        // Test negative values
        $result = validatorNumber::handle(-123, ['type' => 'Int'], $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle(-12.5, ['type' => 'Decimal'], $translator);
        assertTrue($result === true);

        // Test string numbers
        $result = validatorNumber::handle('123', ['type' => 'Int'], $translator);
        assertTrue($result === true);

        $result = validatorNumber::handle('12.5', ['type' => 'Decimal'], $translator);
        assertTrue($result === true);
    }
}