<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation;

use InvalidArgumentException;

final readonly class ValidationIssue
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private string $code,
        private string $message,
        private ValidationSeverity $severity,
        private ?string $module = null,
        private array $context = [],
    ) {
        if (trim($this->code) === '') {
            throw new InvalidArgumentException(
                'A validation issue must have a code.',
            );
        }

        if (trim($this->message) === '') {
            throw new InvalidArgumentException(
                'A validation issue must have a message.',
            );
        }

        if (
            $this->module !== null
            && trim($this->module) === ''
        ) {
            throw new InvalidArgumentException(
                'A validation issue module cannot be empty.',
            );
        }
    }

    public function code(): string
    {
        return trim($this->code);
    }

    public function message(): string
    {
        return trim($this->message);
    }

    public function severity(): ValidationSeverity
    {
        return $this->severity;
    }

    public function module(): ?string
    {
        return $this->module === null
            ? null
            : trim($this->module);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * @return array{
     *     code: string,
     *     message: string,
     *     severity: string,
     *     module: string|null,
     *     context: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code(),
            'message' => $this->message(),
            'severity' => $this->severity->value,
            'module' => $this->module(),
            'context' => $this->context,
        ];
    }
}