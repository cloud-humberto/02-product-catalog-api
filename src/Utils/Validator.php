<?php
declare(strict_types=1);

namespace Api\Utils;

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $customMessage = null): self
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field][] = $customMessage ?? "Field '{$field}' is required.";
        }
        return $this;
    }

    public function numeric(string $field, float $min = null, float $max = null): self
    {
        if (isset($this->data[$field])) {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field][] = "Field '{$field}' must be a valid number.";
            } else {
                $val = (float)$this->data[$field];
                if ($min !== null && $val < $min) {
                    $this->errors[$field][] = "Field '{$field}' must be at least {$min}.";
                }
                if ($max !== null && $val > $max) {
                    $this->errors[$field][] = "Field '{$field}' cannot exceed {$max}.";
                }
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "Field '{$field}' must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field][] = "Field '{$field}' must be at least {$min} characters long.";
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
