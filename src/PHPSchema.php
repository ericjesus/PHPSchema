<?php

class PHPSchema
{
    private array $config;

    public function __construct()
    {
      $this->config = json_decode(file_get_contents(__DIR__ . '/configs/phpschema.json'), true);
    }

    public function check(array|string $input, array $schema): bool|array
    {
      $data = $this->normalizeInput($input);

      return $this::checkDataSchema($data, $schema, $this->config['locale']);
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

    public static function checkDataSchema(array $input, array $schema, string $locale) : bool|array
    {
      echo $locale;
      return true;
    }
}
