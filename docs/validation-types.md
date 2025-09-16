# Validation Types

PHPSchema supports various data types with comprehensive validation rules. Each type has specific parameters and constraints.

## Text Types

### String

General text validation with length constraints.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"String"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |
| `min_length` | `integer` | Minimum character count | ❌ |
| `max_length` | `integer` | Maximum character count | ❌ |

**Example:**
```php
'name' => [
    'type' => 'String',
    'required' => true,
    'min_length' => 2,
    'max_length' => 50
]
```

### Email

Email address validation with format checking.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Email"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'email' => [
    'type' => 'Email',
    'required' => true
]
```

**Valid emails:** `user@example.com`, `test.email+tag@domain.co.uk`
**Invalid emails:** `invalid-email`, `@domain.com`, `user@`

### Name

Name validation with letter-only constraint and full name requirement.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Name"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'full_name' => [
    'type' => 'Name',
    'required' => true
]
```

**Validation Rules:**
- Only letters and spaces allowed
- Must contain at least first name and surname
- Supports international characters (À-ÖØ-öø-ÿ)

**Valid names:** `John Doe`, `María García`, `José da Silva`
**Invalid names:** `John123`, `John`, `John@Doe`

### URL

URL format validation.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Url"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'website' => [
    'type' => 'Url',
    'required' => false
]
```

**Valid URLs:** `https://example.com`, `http://localhost:8080`, `ftp://files.example.com`

### Enum

Validates against a predefined list of values.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Enum"` | ✅ |
| `options` | `array` | List of valid values | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'status' => [
    'type' => 'Enum',
    'options' => ['active', 'inactive', 'pending'],
    'required' => true
]
```

### Phone

Brazilian phone number validation supporting mobile and landline formats.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Phone"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'phone_number' => [
    'type' => 'Phone',
    'required' => true
]
```

**Validation Rules:**
- Supports Brazilian phone formats with or without country code
- Mobile: 11 digits (11987654321) or with country code (5511987654321)
- Landline: 10 digits (1134567890) or with country code (551134567890)
- Formatting characters are ignored during validation

**Valid phones:** `11987654321`, `(11) 98765-4321`, `+55 11 9 8765-4321`

### CPF

Brazilian individual taxpayer registry (CPF) validation.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"CPF"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'taxpayer_id' => [
    'type' => 'CPF',
    'required' => true
]
```

**Validation Rules:**
- Must be exactly 11 digits
- Uses modulo-11 checksum algorithm
- Rejects known invalid sequences (like 111.111.111-11)
- Formatting characters are ignored during validation

**Valid CPFs:** `123.456.789-09`, `12345678909`

### CNPJ

Brazilian company registry (CNPJ) validation.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"CNPJ"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'company_id' => [
    'type' => 'CNPJ',
    'required' => true
]
```

**Validation Rules:**
- Must be exactly 14 digits
- Uses weighted checksum algorithm
- Rejects known invalid sequences
- Formatting characters are ignored during validation

**Valid CNPJs:** `11.222.333/0001-81`, `11222333000181`

### UUID

Universal Unique Identifier (UUID) version 4 validation.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"UUID"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'unique_id' => [
    'type' => 'UUID',
    'required' => true
]
```

**Validation Rules:**
- Must follow UUID v4 format: `xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`
- Where `y` is 8, 9, A, or B (case-insensitive)
- Exactly 36 characters including hyphens

**Valid UUIDs:** `550e8400-e29b-41d4-a716-446655440000`, `f47ac10b-58cc-4372-a567-0e02b2c3d479`

## Numeric Types

### Integer

Whole number validation with range constraints.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Int"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |
| `min_value` | `integer` | Minimum allowed value | ❌ |
| `max_value` | `integer` | Maximum allowed value | ❌ |
| `min_length` | `integer` | Minimum digit count | ❌ |
| `max_length` | `integer` | Maximum digit count | ❌ |

**Example:**
```php
'age' => [
    'type' => 'Int',
    'required' => true,
    'min_value' => 18,
    'max_value' => 120
],
'phone' => [
    'type' => 'Int',
    'min_length' => 10,
    'max_length' => 15
]
```

### Decimal

Floating-point number validation.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Decimal"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'price' => [
    'type' => 'Decimal',
    'required' => true
]
```

**Valid decimals:** `10.99`, `0.5`, `-15.75`, `100`
**Invalid decimals:** `10.99.5`, `abc`, `10,99`

### Boolean

Boolean value validation with multiple format support.

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `type` | `string` | Must be `"Bool"` | ✅ |
| `required` | `boolean` | Field is mandatory | ❌ |
| `not_empty` | `boolean` | Field cannot be empty | ❌ |

**Example:**
```php
'is_active' => [
    'type' => 'Bool',
    'required' => true
]
```

**Valid values:** `true`, `false`, `1`, `0`, `"1"`, `"0"`, `"true"`, `"false"`

## Schema Types

### Array Schema

Validates arrays where each element follows the same schema.

**Syntax:**
```php
'field_name' => [
    'type' => [/* schema definition */],
    'required' => boolean,
    'not_empty' => boolean
]
```

**Example:**
```php
// Define product schema
$productSchema = [
    'name' => ['type' => 'String', 'required' => true],
    'price' => ['type' => 'Decimal', 'required' => true],
    'category' => ['type' => 'Enum', 'options' => ['electronics', 'books']]
];

// Use in array validation
'products' => [
    'type' => [$productSchema],
    'required' => true,
    'not_empty' => true
]
```

**Data Example:**
```php
[
    'products' => [
        [
            'name' => 'Smartphone',
            'price' => 699.99,
            'category' => 'electronics'
        ],
        [
            'name' => 'PHP Book',
            'price' => 29.99,
            'category' => 'books'
        ]
    ]
]
```

### Object Schema

Validates nested objects with their own schema.

**Example:**
```php
'user' => [
    'type' => [
        'name' => ['type' => 'String', 'required' => true],
        'email' => ['type' => 'Email', 'required' => true],
        'profile' => [
            'type' => [
                'bio' => ['type' => 'String', 'max_length' => 500],
                'age' => ['type' => 'Int', 'min_value' => 13]
            ]
        ]
    ]
]
```

## Common Parameters

These parameters are available for all types:

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `required` | `boolean` | Field must be present | `false` |
| `not_empty` | `boolean` | Field cannot be empty/null | `false` |

## Validation Examples

### User Registration Schema

```php
$userSchema = [
    'username' => [
        'type' => 'String',
        'required' => true,
        'min_length' => 3,
        'max_length' => 20
    ],
    'email' => [
        'type' => 'Email',
        'required' => true
    ],
    'password' => [
        'type' => 'String',
        'required' => true,
        'min_length' => 8
    ],
    'age' => [
        'type' => 'Int',
        'required' => true,
        'min_value' => 13
    ],
    'terms_accepted' => [
        'type' => 'Bool',
        'required' => true
    ]
];
```

### Product Catalog Schema

```php
$productSchema = [
    'name' => [
        'type' => 'String',
        'required' => true,
        'max_length' => 100
    ],
    'description' => [
        'type' => 'String',
        'max_length' => 1000
    ],
    'price' => [
        'type' => 'Decimal',
        'required' => true
    ],
    'category' => [
        'type' => 'Enum',
        'options' => ['electronics', 'clothing', 'books', 'home'],
        'required' => true
    ],
    'tags' => [
        'type' => [['type' => 'String']], // Array of strings
        'required' => false
    ]
];
```

### API Response Schema

```php
$apiResponseSchema = [
    'status' => [
        'type' => 'Enum',
        'options' => ['success', 'error'],
        'required' => true
    ],
    'data' => [
        'type' => [$productSchema], // Array of products
        'required' => false
    ],
    'message' => [
        'type' => 'String',
        'required' => false
    ],
    'timestamp' => [
        'type' => 'Int',
        'required' => true
    ]
];
```

## Next Steps

- Check out the [demo](../demo) folder for a working example