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

    public function check(array|string $input, array $schema): bool|array
    {
      $data = $this->normalizeInput($input);

      return $this->validate($data, $schema);
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

    private function validate(array $data, array $schema) : bool|array
    {
      $fails = array();

      foreach ($schema as $field => $rules) {

        if (!isset($data[$field]) && !empty($rules['required'])) {
          $fails['error'][$field] = "Is required.";
          continue;
        }

        if (!isset($data[$field])) {
          continue; // campo opcional ausente
        }

        $value = isset($data[$field]) ? $data[$field] : null;

        // Se o campo é vazio e não é obrigatório, ignora
        if ($value === null || $value === '') {
          if (empty($rules['required']) || (isset($rules['not_empty']) && $rules['not_empty'] === false)) {
            continue; // pula validação
          }
        }

        // Decodifica JSON apenas se não estiver vazio
        $valueDecode = ($value !== '') ? json_decode(html_entity_decode($value), true) : $value;

        // ===== Detecta se o type é um sub-schema =====
        if (isset($rules['type'])) {
          $subSchema = null;

          // 1️⃣ Array de schemas
          if (is_array($rules['type']) && isset($rules['type'][0]) && is_array($rules['type'][0])) {
            $subSchema = $rules['type'][0];

            // Se estiver vazio e not_empty=false, ignora
            if ($valueDecode === '' && (!isset($rules['not_empty']) || $rules['not_empty'] === false)) {
              continue;
            }

            if (!is_array($valueDecode) || array_keys($valueDecode) !== range(0, count($valueDecode) - 1)) {
              $fails['error'][$field] = "Must be an array of items.";
            } else {
              foreach ($valueDecode as $idx => $item) {
                $res = self::validate($item, $subSchema);
                if ($res !== true) {
                  $fails['error'][$field][$idx] = $res['error'];
                }
              }
            }
            continue;
          }

          // 2️⃣ Único sub-schema (array associativo)
          if (is_array($rules['type'])) {
            $subSchema = $rules['type'];

            if ($valueDecode === '' && (!isset($rules['not_empty']) || $rules['not_empty'] === false)) {
              continue;
            }

            if (!is_array($valueDecode)) {
              $fails['error'][$field] = "Must be an object matching the schema.";
            } else {
              $res = self::validate($valueDecode, $subSchema);
              if ($res !== true) {
                $fails['error'][$field] = $res['error'];
              }
            }
            continue;
          }
        }

        // ===== Validação de tipos simples =====
        if (isset($rules['not_empty']) && $rules['not_empty'] && empty($value) && $value != 0) {
          $fails['error'][$field] = "Field cannot be empty";
        }

        switch ($rules['type']) {
          case 'Email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
              $fails['error'][$field] =  "Must be a valid email.";
            }
            break;
          case 'String':
            if (!is_string($value)) {
              $fails['error'][$field] =  "Must be a string.";
            }
            break;
          case 'Int':
            if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
              $fails['error'][$field] = "Must be a valid integer.";
            }
            break;
          case 'Name':
            $nameValidation = validatorText::validadeName($value);
            if ($nameValidation !== true) {
              $fails['error'][$field] = $nameValidation;
            }
            break;
          case 'Url':
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
              $fails['error'][$field] = "Invalid URL format for '$field'.";
            }
            break;
          case 'Enum':
            if(!in_array($value, $rules['options'])) {
              $fails['error'][$field] = "Invalid option, '$value' is invalid.";
            }
            break;
          case 'Bool':
            if (!is_bool($value) && !in_array($value, [0, 1, "0", "1", "true", "false"], true)) {
              $fails['error'][$field] = "Must be a valid boolean (true/false or 0/1).";
            }
            break;
          case 'Decimal':
            if (!is_numeric($value) || !preg_match('/^-?\d+(\.\d+)?$/', (string)$value)) {
              $fails['error'][$field] = "Must be a valid decimal number.";
            }
            break;
        }

        // ===== Validações adicionais =====
        if (in_array($rules['type'], ['String', 'Email', 'Name'])) {
          if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
            $fails['error'][$field] =  "Must exceed {$rules['min_length']} characters.";
          }
          if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
            $fails['error'][$field] =  "Must not exceed {$rules['max_length']} characters.";
          }
        }

        if ($rules['type'] == 'Int') {
          if (isset($rules['min_value']) && $value < $rules['min_value']) {
            $fails['error'][$field] = "Must be at least {$rules['min_value']}.";
          }
          if (isset($rules['max_value']) && $value > $rules['max_value']) {
            $fails['error'][$field] = "Must not exceed {$rules['max_value']}.";
          }

          $valueStr = (string) $value;
          if (isset($rules['min_length']) && strlen($valueStr) < $rules['min_length']) {
            $fails['error'][$field] =  "Must exceed {$rules['min_length']} characters.";
          }
          if (isset($rules['max_length']) && strlen($valueStr) > $rules['max_length']) {
            $fails['error'][$field] =  "Must not exceed {$rules['max_length']} characters.";
          }
        }
      }

      return count($fails) > 0 ? $fails : true;
    }
}
