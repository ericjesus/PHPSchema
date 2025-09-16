# Configuration

PHPSchema behavior can be customized through the configuration file located at `src/configs/phpschema.json`.

## Configuration File Location

```
src/configs/phpschema.json
```

## Default Configuration

```json
{
  "locale": "pt_BR",
  "schemas-path": "schemas",
  "input-type": "array",
  "validation-mode": "collect-all"
}
```

## Configuration Parameters

### `locale`

Controls the language for validation error messages.

| Parameter | Type | Description |
|-----------|------|-------------|
| **locale** | `string` | Language code for error messages |

**Available Options:**
- `pt_BR` - Portuguese (Brazil)
- `en_US` - English (United States)  
- `es_ES` - Spanish (Spain)

**Example:**
```json
{
  "locale": "en_US"
}
```

**Result:**
- Portuguese: `"É obrigatório."`
- English: `"Is required."`
- Spanish: `"Es obligatorio."`

---

### `schemas-path`

Defines the directory where your schema files are located.

| Parameter | Type | Description |
|-----------|------|-------------|
| **schemas-path** | `string` | Relative path to schema files directory |

**Example:**
```json
{
  "schemas-path": "my-schemas"
}
```

**Directory Structure:**
```
project/
├── src/PHPSchema/
└── my-schemas/          # Custom schemas location
    ├── schema.user.php
    └── schema.product.php
```

---

### `input-type`

Specifies the expected format of input data.

| Parameter | Type | Description |
|-----------|------|-------------|
| **input-type** | `string` | Expected input data format |

**Available Options:**

| Option | Description | Example |
|--------|-------------|---------|
| `array` | PHP associative array | `['name' => 'John', 'age' => 25]` |
| `json` | JSON string | `'{"name":"John","age":25}'` |

**Usage Example:**
```php
// input-type: "array"
$data = ['name' => 'John', 'email' => 'john@example.com'];
$result = $phpSchema->check($data, $schema);

// input-type: "json"  
$jsonData = '{"name":"John","email":"john@example.com"}';
$result = $phpSchema->check($jsonData, $schema);
```

---

### `validation-mode`

Controls how validation errors are collected and returned.

| Parameter | Type | Description |
|-----------|------|-------------|
| **validation-mode** | `string` | Error collection strategy |

**Available Options:**

| Mode | Description | Behavior |
|------|-------------|----------|
| `fail-fast` | Stop on first error | Returns immediately when first validation fails |
| `collect-all` | Collect all errors | Validates all fields and returns all errors found |

**Example - Fail Fast:**
```json
{
  "validation-mode": "fail-fast"
}
```

```php
$data = [
    'name' => '',      // Error 1: required
    'email' => 'bad',  // Error 2: invalid email
    'age' => 15        // Error 3: below minimum
];

// Result with fail-fast: Only first error returned
[
    'error' => [
        'name' => 'Is required.'
    ]
]
```

**Example - Collect All:**
```json
{
  "validation-mode": "collect-all"
}
```

```php
// Result with collect-all: All errors returned
[
    'error' => [
        'name' => 'Is required.',
        'email' => 'Must be a valid email.',
        'age' => 'Must be at least 18.'
    ]
]
```

## Custom Translations

To add custom translations:

1. Create a new translation file in `src/core/translations/`
2. Follow the existing structure (see [Translations](translations.md))
3. Update the `locale` parameter to use your custom language

## Troubleshooting

### Common Configuration Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| `Config file not found` | Missing phpschema.json | Ensure file exists in `src/configs/` |
| `Invalid config format` | Malformed JSON | Validate JSON syntax |
| `Translation file not found` | Invalid locale code | Check if translation file exists |
| `Invalid schemas path` | Wrong directory path | Verify the schemas directory exists |

## Next Steps

- Explore all available [Validation Types](validation-types.md)
- Check out the [demo](../demo) folder for a working example