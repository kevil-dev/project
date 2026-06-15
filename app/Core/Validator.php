<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(string $field, string $value): void
    {
        if (trim($value) === '') {
            $this->errors[$field] = 'The ' . $field . ' field is required';
        }
    }

    public function maxLength(string $field, string $value, int $max): void
    {
        if (strlen($value) > $max) {
            $this->errors[$field] = 'The ' . $field . ' must not exceed ' . $max . ' characters';
        }
    }

    public function positiveNumber(string $field, string $value): void
    {
        if (!is_numeric($value) || (float) $value <= 0) {
            $this->errors[$field] = 'The ' . $field . ' must be a positive number';
        }
    }

    public function positiveInteger(string $field, string $value): void
    {
        if (!ctype_digit($value) || (int) $value <= 0) {
            $this->errors[$field] = 'The ' . $field . ' must be a positive integer';
        }
    }

    public function image(string $field, array $file): void
    {
        // No file uploaded
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[$field] = 'An image is required';
            return;
        }

        // Upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$field] = 'Image upload failed';
            return;
        }

        // Max 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->errors[$field] = 'Image must not exceed 2MB';
            return;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType     = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $this->errors[$field] = 'Image must be jpeg, png, or webp';
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function stockType(string $field, string $value): void
    {
        if (!in_array($value, ['restock', 'adjustment'], true)) {
            $this->errors[$field] = 'Type must be restock or adjustment';
        }
    }

    public function nonZeroInteger(string $field, mixed $value): void
    {
        if (!is_int($value) || $value === 0) {
            $this->errors[$field] = 'The ' . $field . ' must be a non-zero integer';
        }
    }
}
