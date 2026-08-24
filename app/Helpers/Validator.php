<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function validate(array $data, array $rules): self
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $rulesList = explode('|', $fieldRules);
            $value = $data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return $this;
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "The {$field} field is required.");
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$field} must be a valid email address.");
                }
                break;

            case 'min':
                $min = (int) $params[0];
                if (is_string($value) && strlen($value) < $min) {
                    $this->addError($field, "The {$field} must be at least {$min} characters.");
                }
                if (is_numeric($value) && $value < $min) {
                    $this->addError($field, "The {$field} must be at least {$min}.");
                }
                break;

            case 'max':
                $max = (int) $params[0];
                if (is_string($value) && strlen($value) > $max) {
                    $this->addError($field, "The {$field} may not be greater than {$max} characters.");
                }
                break;

            case 'confirmed':
                if ($value !== ($data[$field . '_confirmation'] ?? null)) {
                    $this->addError($field, "The {$field} confirmation does not match.");
                }
                break;

            case 'unique':
                [$table, $column] = $params;
                $column = $column ?: $field;
                $db = \App\Config\Database::getInstance()->getConnection();
                $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
                $stmt = $db->prepare($sql);
                $stmt->execute(['value' => $value]);
                if ($stmt->fetchColumn() > 0) {
                    $this->addError($field, "The {$field} has already been taken.");
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params)) {
                    $this->addError($field, "The selected {$field} is invalid.");
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, "The {$field} must be a number.");
                }
                break;

            case 'regex':
                if ($value && !preg_match($params[0], $value)) {
                    $this->addError($field, "The {$field} format is invalid.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    public static function sanitize(string $str): string
    {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
}