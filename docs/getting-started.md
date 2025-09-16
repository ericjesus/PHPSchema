# Getting Started

Welcome to PHPSchema! This guide will help you get started with data validation in PHP.

## Requirements

- **PHP 8.0+** (Required for union types and modern syntax)

## Installation

### Method 1: Direct Download

1. Download or clone the PHPSchema repository:
   ```bash
   git clone https://github.com/ericjesus/PHPSchema.git
   ```

2. Include the main class in your project:
   ```php
   require_once 'path/to/PHPSchema/src/PHPSchema.php';
   ```

## Directory Structure

After installation, your PHPSchema directory should look like this:

```
PHPSchema/
├── src/
│   ├── PHPSchema.php              # Main class
│   ├── configs/
│   │   └── phpschema.json         # Configuration file
│   └── core/
│       ├── translations/          # Language files
│       │   ├── pt_BR.json
│       │   ├── en_US.json
│       │   └── es_ES.json
│       └── validators/             # Validation classes
│           ├── validator.text.php
│           ├── validator.number.php
│           ├── validator.common.php
│           └── validator.schema.php
├── schemas/                       # Your schema files
│   └── schema.sample.php
├── demo/                          # Working examples
│   ├── index.html
│   └── api.php
└── docs/                          # Documentation
```

## First Test

Create a simple test file to verify installation:

```php
<?php
// test.php
require_once 'src/PHPSchema.php';

try {
    $phpSchema = new PHPSchema();
    echo "✅ PHPSchema installed successfully!";
    
    // Quick validation test
    $schema = ['name' => ['type' => 'String', 'required' => true]];
    $data = ['name' => 'Test'];
    
    $result = $phpSchema->check($data, $schema);
    
    if ($result === true) {
        echo "\n✅ Validation working correctly!";
    }
    
} catch (Exception $e) {
    echo "❌ Installation error: " . $e->getMessage();
}
```

Run the test:
```bash
php test.php
```

## Basic Usage

### Simple Example

```php
<?php
require_once 'src/PHPSchema.php';

// Create PHPSchema instance
$phpSchema = new PHPSchema();

// Define your validation schema
$userSchema = [
    'name' => [
        'type' => 'String',
        'required' => true,
        'min_length' => 2,
        'max_length' => 50
    ],
    'email' => [
        'type' => 'Email',
        'required' => true
    ],
    'age' => [
        'type' => 'Int',
        'required' => true,
        'min_value' => 18,
        'max_value' => 100
    ]
];

// Sample data to validate
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25
];

// Perform validation
$result = $phpSchema->check($userData, $userSchema);

if ($result === true) {
    echo "✅ Data is valid!";
} else {
    echo "❌ Validation failed:";
    print_r($result);
}
```

## Strict Mode

Enable strict mode to prevent extra fields not defined in your schema:

```php
// This will fail because 'extra_field' is not in schema
$data = [
    'name' => 'John',
    'email' => 'john@example.com',
    'extra_field' => 'not allowed'
];

$result = $phpSchema->check($data, $userSchema, true); // strict mode = true
```

## Error Handling

When validation fails, PHPSchema returns detailed error information:

```php
$invalidData = [
    'name' => 'A', // too short
    'email' => 'invalid-email', // invalid format
    'age' => 15 // below minimum
];

$result = $phpSchema->check($invalidData, $userSchema);

// Result structure:
// [
//     'error' => [
//         'name' => 'Must be more than 2 characters.',
//         'email' => 'Must be a valid email.',
//         'age' => 'Must be at least 18.'
//     ]
// ]
```

## Web Server Setup (Optional)

If you want to test the demo:

1. Place PHPSchema in your web server directory
2. Navigate to `http://localhost/PHPSchema/demo/`
3. Test the validation examples

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| `Class 'PHPSchema' not found` | Check the require_once path |
| `Parse error` | Ensure PHP 8.0+ is installed |
| `Config file not found` | Verify the `src/configs/` directory exists |
| `Translation file not found` | Check `src/core/translations/` directory |

### PHP Version Check

```php
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    die('PHPSchema requires PHP 8.0 or higher');
}
```

## Need Help?

- Check the documentation for detailed information
- Review all [Validation Types](validation-types.md) available

## Next Steps

- Learn about [Configuration](configuration.md) options
- Explore all available [Validation Types](validation-types.md)
- Check out the [demo](../demo) folder for a working example