<?php

class PHPSchema
{
    private array $config;
    private array $translations;

    public function __construct()
    {
      $this->config = $this->getConfig();

      $this->translations = $this->getTranslations($this->config['locale']);

      $this->importSchemasFromPath($this->config['schemas-path']);
    }

    public function check(array|string $input, array $schema, bool $strictMode = false): bool|array
    {
      $data = $this->normalizeInput($input);

      return $this->validate($data, $schema, $strictMode);
    }

    private function getConfig(): array
    {
      json_decode(file_get_contents(__DIR__ . '/configs/phpschema.json'), true);

      $file = __DIR__ . '/configs/phpschema.json';

      if (!file_exists($file)) {
        throw new RuntimeException("Config file not found: $file");
      }

      $configs = json_decode(file_get_contents($file), true);

      if (!is_array($configs)) {
        throw new RuntimeException("Invalid config file format: $file");
      }

      return $configs;
    }

    private function getTranslations(string $locale): array
    {
      $file = __DIR__ . '/core/translations/' . $locale . '.json';

      if (!file_exists($file)) {
        throw new RuntimeException("Translation file not found: $file");
      }

      $translations = json_decode(file_get_contents($file), true);

      if (!is_array($translations)) {
        throw new RuntimeException("Invalid translation file format: $file");
      }

      return $translations;
    }

    private function importSchemasFromPath(string $path): void
    {
      $path = dirname(__DIR__, 1) . '/' . $path;

      if (!is_dir($path)) {
        throw new InvalidArgumentException("Invalid schemas path: $path");
      }

      $files = glob($path . '/*.php');

      foreach ($files as $file) {
        require_once $file;
      }

      // Include validation classes
      require_once __DIR__ . '/core/validators/validator.text.php';
      require_once __DIR__ . '/core/validators/validator.number.php';
      require_once __DIR__ . '/core/validators/validator.common.php';
      require_once __DIR__ . '/core/validators/validator.schema.php';
    }

    private function normalizeInput(array|string $input): array
    {
      $inputType = $this->config['input-type'];

      switch ($inputType) {
        case 'array':
          if (!is_array($input)) {
            throw new InvalidArgumentException("Input is not an array: $inputType");
          }
          return $input;
        case 'json':
          $decoded = json_decode($input, true);
          if (!is_array($decoded)) {
            throw new InvalidArgumentException("Input is not a valid JSON array: $inputType");
          }
          return $decoded;
        default:
          throw new InvalidArgumentException("Invalid input type: $inputType");
      }
    }

    private function validate(array $data, array $schema, bool $strictMode = true) : bool|array
    {
      $fails = array();
      $validationMode = $this->config['validation-mode'] ?? 'fail-fast';

      // Strict mode validation: check for extra fields not defined in schema
      if ($strictMode) {
        $extraFields = array_diff(array_keys($data), array_keys($schema));
        if (!empty($extraFields)) {
          foreach ($extraFields as $extraField) {
            $fails['error'][$extraField] = "Field is not allowed in strict mode.";
            
            // Fail-fast mode: return immediately on first error
            if ($validationMode === 'fail-fast') {
              return $fails;
            }
          }
        }
      }

      foreach ($schema as $field => $rules) {
        $value = isset($data[$field]) ? $data[$field] : null;

        // Basic validations: required and not_empty
        $basicValidation = $this->validateBasicRules($value, $rules, $field);
        if ($basicValidation !== true) {
          $fails['error'][$field] = $basicValidation;
          
          // Fail-fast mode: return immediately on first error
          if ($validationMode === 'fail-fast') {
            return $fails;
          }
          continue;
        }

        // If field doesn't exist and is not required, skip
        if (!isset($data[$field])) {
          continue; // optional field missing
        }

        // If field is empty and allowed to be empty, skip
        if (($value === null || $value === '') && $this->canSkipValidation($rules)) {
          continue;
        }

        // Type validation
        if (isset($rules['type'])) {
          $validationResult = $this->delegateValidation($value, $rules, $field, $strictMode);
          if ($validationResult !== true) {
            $fails['error'][$field] = $validationResult;
            
            // Fail-fast mode: return immediately on first error
            if ($validationMode === 'fail-fast') {
              return $fails;
            }
          }
        }
      }

      return count($fails) > 0 ? $fails : true;
    }

    private function validateBasicRules($value, array $rules, string $field): string|bool
    {
      // Required validation
      $requiredValidation = validatorCommon::validateRequired($value, !empty($rules['required']));
      if ($requiredValidation !== true) {
        return $requiredValidation;
      }

      // Not empty validation
      if (isset($rules['not_empty'])) {
        return validatorCommon::validateNotEmpty($value, $rules['not_empty']);
      }

      return true;
    }

    private function canSkipValidation(array $rules): bool
    {
      return empty($rules['required']) || (isset($rules['not_empty']) && $rules['not_empty'] === false);
    }

    private function delegateValidation($value, array $rules, string $field, bool $strictMode = true): string|bool
    {
      $type = $rules['type'];

      // If it's a sub-schema (array), delegate to validatorSchema
      if (is_array($type)) {
        return validatorSchema::handle($value, $rules, $field, [$this, 'validate'], $strictMode);
      }

      // Delegate to specific validators based on type
      switch ($type) {
        case 'String':
        case 'Email':
        case 'Name':
        case 'Url':
        case 'Enum':
          return validatorText::handle($value, $rules, $field);
        
        case 'Int':
        case 'Decimal':
        case 'Bool':
          return validatorNumber::handle($value, $rules, $field);
        
        default:
          throw new InvalidArgumentException("Invalid type: $type");
      }
    }
}
