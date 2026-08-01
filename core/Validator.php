<?php

namespace Core;

class Validator
{
    protected array $data;
    protected array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleString) {
            $rulesForField = explode('|', $ruleString);

            foreach ($rulesForField as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    protected function applyRule(string $field, string $rule): void
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
        $value = $this->data[$field] ?? null;

        switch ($name) {
            case 'required':
                if ($value === null || trim((string) $value) === '') {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Enter a valid email address.');
                }
                break;

            case 'min':
                if ($value !== null && strlen((string) $value) < (int) $param) {
                    $this->addError($field, ucfirst($field) . " must be at least {$param} characters.");
                }
                break;

            case 'max':
                if ($value !== null && strlen((string) $value) > (int) $param) {
                    $this->addError($field, ucfirst($field) . " must not exceed {$param} characters.");
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be numeric.');
                }
                break;

            case 'phone':
                if ($value !== null && $value !== '' && !preg_match('/^[6-9]\d{9}$/', (string) $value)) {
                    $this->addError($field, 'Enter a valid 10-digit mobile number.');
                }
                break;

            case 'date':
                if ($value !== null && $value !== '' && !self::isValidDate((string) $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date.');
                }
                break;

            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid URL.');
                }
                break;

            case 'confirmed':
                $confirmField = "{$field}_confirmation";
                if (($this->data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, ucfirst($field) . ' confirmation does not match.');
                }
                break;

            case 'unique':
                [$table, $column] = array_pad(explode(',', (string) $param), 2, 'id');
                if ($value !== null && $value !== '' && $this->exists($table, $column, $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is already taken.');
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $param);
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->addError($field, 'Invalid value for ' . str_replace('_', ' ', $field) . '.');
                }
                break;
        }
    }

    protected function exists(string $table, string $column, mixed $value): bool
    {
        $stmt = Database::connection()->prepare("SELECT 1 FROM {$table} WHERE {$column} = :value LIMIT 1");
        $stmt->execute(['value' => $value]);

        return $stmt->fetchColumn() !== false;
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * strtotime() alone is too lenient here - strtotime('0000-00-00')
     * returns a real (large negative) timestamp instead of false, so a
     * literal zero-date silently passed as "valid" - checkdate() catches
     * that while still accepting genuine calendar dates.
     */
    protected static function isValidDate(string $value): bool
    {
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return false;
        }

        return checkdate((int) date('n', $timestamp), (int) date('j', $timestamp), (int) date('Y', $timestamp));
    }
}
