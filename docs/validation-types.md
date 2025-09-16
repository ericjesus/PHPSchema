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