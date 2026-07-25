<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation;

use InvalidArgumentException;
use Northpole\Runtime\Validation\Contracts\ValidationRuleContract;
use Throwable;

final class RuntimeValidationEngine
{
    /**
     * @var array<string, ValidationRuleContract>
     */
    private array $rules = [];

    public function register(
        ValidationRuleContract $rule,
    ): self {
        $name = trim(
            $rule->name(),
        );

        if ($name === '') {
            throw new InvalidArgumentException(
                'A runtime validation rule must have a name.',
            );
        }

        if (isset($this->rules[$name])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Runtime validation rule [%s] is already registered.',
                    $name,
                ),
            );
        }

        $this->rules[$name] = $rule;

        return $this;
    }

    /**
     * @param  iterable<int, ValidationRuleContract>  $rules
     */
    public function registerMany(
        iterable $rules,
    ): self {
        foreach ($rules as $rule) {
            $this->register($rule);
        }

        return $this;
    }

    public function has(
        string $name,
    ): bool {
        return isset(
            $this->rules[trim($name)],
        );
    }

    public function count(): int
    {
        return count($this->rules);
    }

    /**
     * @return array<string, ValidationRuleContract>
     */
    public function rules(): array
    {
        $rules = $this->rules;

        ksort($rules);

        return $rules;
    }

    public function validate(): ValidationResult
    {
        $result = new ValidationResult;

        foreach ($this->rules() as $name => $rule) {
            try {
                $result->merge(
                    $rule->validate(),
                );
            } catch (Throwable $exception) {
                $result->add(
                    new ValidationIssue(
                        code: 'validation.rule_failed',
                        message: sprintf(
                            'Validation rule [%s] failed: %s',
                            $name,
                            $exception->getMessage(),
                        ),
                        severity: ValidationSeverity::Error,
                        context: [
                            'rule' => $name,
                            'exception' => $exception::class,
                        ],
                    ),
                );
            }
        }

        return $result;
    }

    public function clear(): self
    {
        $this->rules = [];

        return $this;
    }
}