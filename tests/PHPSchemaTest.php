<?php

require_once __DIR__ . '/../src/PHPSchema.php';

class PHPSchemaTest
{
    private PHPSchema $validator;

    public function __construct()
    {
        $this->validator = new PHPSchema();
    }

    public function testBasicValidation()
    {
        // Test valid data
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ];
        $schema = [
            'name' => ['type' => 'String', 'required' => true],
            'email' => ['type' => 'Email', 'required' => true],
            'age' => ['type' => 'Int', 'required' => true]
        ];

        $result = $this->validator->check($data, $schema);
        assertTrue($result === true, 'Valid data should pass');

        // Test invalid data
        $invalidData = [
            'name' => 123, // wrong type
            'email' => 'invalid-email',
            'age' => 'not-a-number'
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Invalid data should return errors');
        assertNotEmpty($result['error'], 'Error array should not be empty');
    }

    public function testRequiredFieldValidation()
    {
        $schema = [
            'name' => ['type' => 'String', 'required' => true],
            'email' => ['type' => 'Email', 'required' => false]
        ];

        // Missing required field
        $data = ['email' => 'john@example.com'];
        $result = $this->validator->check($data, $schema);
        assertArrayHasKey('error', $result, 'Missing required field should fail');
        assertArrayHasKey('name', $result['error'], 'Should detect missing name field');

        // Missing optional field should pass
        $data = ['name' => 'John Doe'];
        $result = $this->validator->check($data, $schema);
        assertTrue($result === true, 'Missing optional field should pass');
    }

    public function testStrictModeValidation()
    {
        $schema = [
            'name' => ['type' => 'String', 'required' => true],
            'email' => ['type' => 'Email', 'required' => true]
        ];

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'extra_field' => 'not allowed'
        ];

        // Non-strict mode should allow extra fields
        $result = $this->validator->check($data, $schema, false);
        assertTrue($result === true, 'Extra fields should be allowed in non-strict mode');

        // Strict mode should reject extra fields
        $result = $this->validator->check($data, $schema, true);
        assertArrayHasKey('error', $result, 'Extra fields should be rejected in strict mode');
        assertArrayHasKey('extra_field', $result['error'], 'Should detect extra field');
    }

    public function testAllValidationTypes()
    {
        $schema = [
            'text' => ['type' => 'String'],
            'email' => ['type' => 'Email'],
            'url' => ['type' => 'Url'],
            'name' => ['type' => 'Name'],
            'status' => ['type' => 'Enum', 'options' => ['active', 'inactive']],
            'phone' => ['type' => 'Phone'],
            'cpf' => ['type' => 'CPF'],
            'cnpj' => ['type' => 'CNPJ'],
            'uuid' => ['type' => 'UUID'],
            'integer' => ['type' => 'Int'],
            'decimal' => ['type' => 'Decimal'],
            'boolean' => ['type' => 'Bool']
        ];

        $validData = [
            'text' => 'Hello World',
            'email' => 'user@example.com',
            'url' => 'https://example.com',
            'name' => 'John Doe',
            'status' => 'active',
            'phone' => '11987654321',
            'cpf' => '123.456.789-09',
            'cnpj' => '11.222.333/0001-81',
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'integer' => 123,
            'decimal' => 12.5,
            'boolean' => true
        ];

        $result = $this->validator->check($validData, $schema);
        assertTrue($result === true, 'All valid data types should pass');

        $invalidData = [
            'text' => 123,
            'email' => 'invalid-email',
            'url' => 'invalid-url',
            'name' => 'John123',
            'status' => 'invalid-status',
            'phone' => 'invalid-phone',
            'cpf' => '123.456.789-00',
            'cnpj' => '11.222.333/0001-00',
            'uuid' => 'invalid-uuid',
            'integer' => 'not-a-number',
            'decimal' => 'not-a-decimal',
            'boolean' => 'not-a-boolean'
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Invalid data should return errors');
        assertEquals(12, count($result['error']), 'Should detect errors in all 12 fields');
    }

    public function testValidationConstraints()
    {
        $schema = [
            'short_text' => ['type' => 'String', 'min_length' => 5, 'max_length' => 10],
            'big_number' => ['type' => 'Int', 'min_value' => 100, 'max_value' => 1000],
            'small_number' => ['type' => 'Int', 'min_value' => 10, 'max_value' => 50] // Changed to Int for min/max validation
        ];

        // Valid data within constraints
        $validData = [
            'short_text' => 'hello',
            'big_number' => 500,
            'small_number' => 25
        ];

        $result = $this->validator->check($validData, $schema);
        assertTrue($result === true, 'Data within constraints should pass');

        // Invalid data outside constraints
        $invalidData = [
            'short_text' => 'hi', // too short
            'big_number' => 50, // too small
            'small_number' => 100 // too big
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Data outside constraints should fail');
        assertEquals(3, count($result['error']), 'Should detect all constraint violations');
    }

    public function testNestedSchemaValidation()
    {
        $schema = [
            'user' => [
                'type' => [
                    'name' => ['type' => 'String', 'required' => true],
                    'email' => ['type' => 'Email', 'required' => true]
                ],
                'required' => true
            ],
            'settings' => [
                'type' => [
                    'theme' => ['type' => 'Enum', 'options' => ['light', 'dark']],
                    'notifications' => ['type' => 'Bool']
                ],
                'required' => false
            ]
        ];

        // Valid nested data
        $validData = [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'settings' => [
                'theme' => 'dark',
                'notifications' => true
            ]
        ];

        $result = $this->validator->check($validData, $schema);
        assertTrue($result === true, 'Valid nested data should pass');

        // Invalid nested data
        $invalidData = [
            'user' => [
                'name' => 'John Doe'
                // missing email
            ],
            'settings' => [
                'theme' => 'invalid-theme',
                'notifications' => 'not-a-boolean'
            ]
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Invalid nested data should fail');
    }

    public function testArraySchemaValidation()
    {
        $schema = [
            'users' => [
                'type' => [
                    [  // Array item schema at index 0
                        'name' => ['type' => 'String', 'required' => true],
                        'age' => ['type' => 'Int', 'required' => true]
                    ]
                ],
                'required' => true
            ]
        ];

        // Valid array data
        $validData = [
            'users' => [
                ['name' => 'John', 'age' => 25],
                ['name' => 'Jane', 'age' => 30]
            ]
        ];

        $result = $this->validator->check($validData, $schema);
        assertTrue($result === true, 'Valid array data should pass');

        // Invalid array data
        $invalidData = [
            'users' => [
                ['name' => 'John', 'age' => 25],
                ['name' => 'Jane'] // missing age
            ]
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Invalid array data should fail');
    }

    public function testNotEmptyValidation()
    {
        $schema = [
            'required_not_empty' => ['type' => 'String', 'required' => true, 'not_empty' => true],
            'optional_can_be_empty' => ['type' => 'String', 'required' => false, 'not_empty' => false]
        ];

        // Valid data
        $validData = [
            'required_not_empty' => 'not empty',
            'optional_can_be_empty' => ''
        ];

        $result = $this->validator->check($validData, $schema);
        assertTrue($result === true, 'Valid not_empty configuration should pass');

        // Invalid data - empty required field
        $invalidData = [
            'required_not_empty' => '', // empty but required and not_empty
            'optional_can_be_empty' => 'can have value'
        ];

        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Empty required field should fail');
        assertArrayHasKey('required_not_empty', $result['error'], 'Should detect empty required field');
    }

    public function testTranslationSystem()
    {
        // The translation system is integrated into the validation
        // This test ensures that error messages are properly translated
        $data = ['email' => 'invalid-email'];
        $schema = ['email' => ['type' => 'Email', 'required' => true]];

        $result = $this->validator->check($data, $schema);
        assertArrayHasKey('error', $result, 'Invalid email should return error');
        assertNotEmpty($result['error']['email'], 'Error message should be present');
        assertTrue(is_string($result['error']['email']), 'Error message should be a string');
    }

    public function testInputTypeHandling()
    {
        $schema = ['name' => ['type' => 'String', 'required' => true]];

        // Test with array input
        $arrayData = ['name' => 'John'];
        $result = $this->validator->check($arrayData, $schema);
        assertTrue($result === true, 'Array input should work');

        // Test with JSON string input (fails because config is set to array mode)
        $jsonData = '{"name": "John"}';
        try {
            $result = $this->validator->check($jsonData, $schema);
            assertTrue(false, 'Should have thrown exception for string input in array mode');
        } catch (InvalidArgumentException $e) {
            assertTrue(str_contains($e->getMessage(), 'Input is not an array'), 'Should throw array input error');
        }

        // Test with invalid JSON (also fails because config is set to array mode)
        $invalidJson = '{"name": "John"'; // missing closing brace
        try {
            $result = $this->validator->check($invalidJson, $schema);
            assertTrue(false, 'Should have thrown exception for string input in array mode');
        } catch (InvalidArgumentException $e) {
            assertTrue(str_contains($e->getMessage(), 'Input is not an array'), 'Should throw array input error');
        }
    }

    public function testEdgeCases()
    {
        $schema = ['field' => ['type' => 'String']];

        // Test with empty data
        $result = $this->validator->check([], $schema);
        assertTrue($result === true, 'Empty data with optional fields should pass');

        // Test with null values
        $data = ['field' => null];
        $result = $this->validator->check($data, $schema);
        assertTrue($result === true, 'Null value for optional field should pass');

        // Test with zero values
        $numberSchema = ['number' => ['type' => 'Int']];
        $data = ['number' => 0];
        $result = $this->validator->check($data, $numberSchema);
        assertTrue($result === true, 'Zero should be a valid number');

        // Test with false values
        $boolSchema = ['flag' => ['type' => 'Bool']];
        $data = ['flag' => false];
        $result = $this->validator->check($data, $boolSchema);
        assertTrue($result === true, 'False should be a valid boolean');
    }

    public function testValidationModes()
    {
        $schema = [
            'field1' => ['type' => 'String', 'required' => true],
            'field2' => ['type' => 'Email', 'required' => true],
            'field3' => ['type' => 'Int', 'required' => true]
        ];

        $invalidData = [
            'field1' => 123, // wrong type
            'field2' => 'invalid-email',
            'field3' => 'not-a-number'
        ];

        // In fail-fast mode, should return on first error
        // In collect-all mode, should return all errors
        // The exact behavior depends on the validation-mode configuration
        $result = $this->validator->check($invalidData, $schema);
        assertArrayHasKey('error', $result, 'Should detect validation errors');
        assertNotEmpty($result['error'], 'Should have error details');
    }
}