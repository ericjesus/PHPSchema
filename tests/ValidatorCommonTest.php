<?php

require_once __DIR__ . '/../src/core/validators/validator.common.php';

class ValidatorCommonTest
{
    private function getTranslator(): callable
    {
        return function(string $category, string $key, array $placeholders = []) {
            $messages = [
                'common' => [
                    'required' => 'Is required.',
                    'not_empty' => 'Field cannot be empty.',
                    'strict_mode' => 'Field is not allowed in strict mode.'
                ]
            ];

            $message = $messages[$category][$key] ?? "Missing translation: {$category}.{$key}";
            
            foreach ($placeholders as $placeholder => $value) {
                $message = str_replace("{{$placeholder}}", $value, $message);
            }
            
            return $message;
        };
    }

    public function testValidateRequired()
    {
        $translator = $this->getTranslator();

        // Required field with value - should pass
        $result = validatorCommon::validateRequired('test', true, $translator);
        assertTrue($result === true, 'Required field with value should pass');

        // Required field with null - should fail
        $result = validatorCommon::validateRequired(null, true, $translator);
        assertEquals('Is required.', $result, 'Required field with null should fail');

        // Required field with empty string - should pass (empty string is set, just empty)
        $result = validatorCommon::validateRequired('', true, $translator);
        assertTrue($result === true, 'Required field with empty string should pass (it is set)');

        // Required field with zero - should pass (zero is a valid value)
        $result = validatorCommon::validateRequired(0, true, $translator);
        assertTrue($result === true, 'Required field with zero should pass');

        // Required field with false - should pass (false is a valid value)
        $result = validatorCommon::validateRequired(false, true, $translator);
        assertTrue($result === true, 'Required field with false should pass');

        // Optional field with null - should pass
        $result = validatorCommon::validateRequired(null, false, $translator);
        assertTrue($result === true, 'Optional field with null should pass');

        // Optional field with empty string - should pass
        $result = validatorCommon::validateRequired('', false, $translator);
        assertTrue($result === true, 'Optional field with empty string should pass');

        // Optional field with value - should pass
        $result = validatorCommon::validateRequired('test', false, $translator);
        assertTrue($result === true, 'Optional field with value should pass');
    }

    public function testValidateNotEmpty()
    {
        $translator = $this->getTranslator();

        // Not empty validation enabled with non-empty value - should pass
        $result = validatorCommon::validateNotEmpty('test', true, $translator);
        assertTrue($result === true, 'Non-empty value should pass');

        // Not empty validation enabled with empty string - should fail
        $result = validatorCommon::validateNotEmpty('', true, $translator);
        assertEquals('Field cannot be empty.', $result, 'Empty string should fail');

        // Not empty validation enabled with null - should pass (null != 0 is false, so condition fails)
        $result = validatorCommon::validateNotEmpty(null, true, $translator);
        assertTrue($result === true, 'Null value should pass (null is considered equivalent to 0)');

        // Not empty validation enabled with zero - should pass (zero is not considered empty)
        $result = validatorCommon::validateNotEmpty(0, true, $translator);
        assertTrue($result === true, 'Zero should not be considered empty');

        // Not empty validation enabled with false - should pass (false != 0 is false, so condition fails)
        $result = validatorCommon::validateNotEmpty(false, true, $translator);
        assertTrue($result === true, 'False should pass (false is considered equivalent to 0)');

        // Not empty validation disabled with empty string - should pass
        $result = validatorCommon::validateNotEmpty('', false, $translator);
        assertTrue($result === true, 'Empty string should pass when validation disabled');

        // Not empty validation disabled with null - should pass
        $result = validatorCommon::validateNotEmpty(null, false, $translator);
        assertTrue($result === true, 'Null should pass when validation disabled');

        // Not empty validation enabled with whitespace - should pass (PHP considers whitespace as non-empty)
        $result = validatorCommon::validateNotEmpty('   ', true, $translator);
        assertTrue($result === true, 'Whitespace-only string should pass (PHP considers it non-empty)');

        // Not empty validation enabled with array - should pass if not empty
        $result = validatorCommon::validateNotEmpty(['item'], true, $translator);
        assertTrue($result === true, 'Non-empty array should pass');

        // Not empty validation enabled with empty array - should fail
        $result = validatorCommon::validateNotEmpty([], true, $translator);
        assertEquals('Field cannot be empty.', $result, 'Empty array should fail');
    }

    public function testValidateStrictMode()
    {
        $translator = $this->getTranslator();

        // Data with only schema fields in strict mode - should pass
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $schema = [
            'name' => ['type' => 'String'],
            'email' => ['type' => 'Email']
        ];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertEmpty($result, 'Valid data in strict mode should pass');

        // Data with extra fields in strict mode - should fail
        $data = ['name' => 'John', 'email' => 'john@example.com', 'extra' => 'field'];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertArrayHasKey('extra', $result, 'Extra field should be detected in strict mode');
        assertEquals('Field is not allowed in strict mode.', $result['extra'], 'Extra field should have correct error message');

        // Data with extra fields in non-strict mode - should pass
        $data = ['name' => 'John', 'email' => 'john@example.com', 'extra' => 'field'];
        $result = validatorCommon::validateStrictMode($data, $schema, false, $translator);
        assertEmpty($result, 'Extra fields should be allowed in non-strict mode');

        // Multiple extra fields in strict mode - should detect all
        $data = ['name' => 'John', 'extra1' => 'field1', 'extra2' => 'field2'];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertArrayHasKey('extra1', $result, 'First extra field should be detected');
        assertArrayHasKey('extra2', $result, 'Second extra field should be detected');
        assertEquals(2, count($result), 'Should detect exactly 2 extra fields');
    }

    public function testEdgeCases()
    {
        $translator = $this->getTranslator();

        // Test with numeric strings
        $result = validatorCommon::validateRequired('0', true, $translator);
        assertTrue($result === true, 'String "0" should be considered a valid value');

        $result = validatorCommon::validateNotEmpty('0', true, $translator);
        assertTrue($result === true, 'String "0" should not be considered empty');

        // Test with boolean values
        $result = validatorCommon::validateRequired(true, true, $translator);
        assertTrue($result === true, 'Boolean true should be a valid required value');

        $result = validatorCommon::validateRequired(false, true, $translator);
        assertTrue($result === true, 'Boolean false should be a valid required value');

        $result = validatorCommon::validateNotEmpty(true, true, $translator);
        assertTrue($result === true, 'Boolean true should not be empty');

        $result = validatorCommon::validateNotEmpty(false, true, $translator);
        assertTrue($result === true, 'Boolean false should not be empty');

        // Test strict mode with nested arrays
        $data = ['user' => ['name' => 'John', 'extra' => 'field']];
        $schema = ['user' => ['type' => ['name' => ['type' => 'String']]]];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertEmpty($result, 'Strict mode should only check top-level fields');

        // Test empty schema
        $data = ['field' => 'value'];
        $schema = [];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertArrayHasKey('field', $result, 'All fields should be extra when schema is empty');
    }

    public function testComplexScenarios()
    {
        $translator = $this->getTranslator();

        // Test combination of required and not_empty
        // Required field with empty value - should pass because required only checks isset()
        $result = validatorCommon::validateRequired('', true, $translator);
        assertTrue($result === true, 'Required validation should pass for empty string (it is set)');

        // Then test not_empty (this would be called separately in real validation)
        $result = validatorCommon::validateNotEmpty('', true, $translator);
        assertEquals('Field cannot be empty.', $result, 'Not empty validation should catch empty string');

        // Test strict mode with case sensitivity
        $data = ['Name' => 'John', 'name' => 'Jane'];
        $schema = ['name' => ['type' => 'String']];
        $result = validatorCommon::validateStrictMode($data, $schema, true, $translator);
        assertArrayHasKey('Name', $result, 'Field names should be case sensitive');
        assertEquals(1, count($result), 'Should detect case-sensitive extra field');
    }
}