<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

use Closure;
use InvalidArgumentException;
use Northpole\Runtime\Repair\Contracts\RepairProviderContract;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;

final class RuntimeRepairEngine
{
    /**
     * @var array<
     *     string,
     *     Closure(ValidationIssue): RepairRecommendation|null
     * >
     */
    private array $resolvers = [];

    /**
     * @var array<int, RepairProviderContract>
     */
    private array $providers = [];

    public function register(
        string $issueCode,
        Closure $resolver,
    ): self {
        $issueCode = trim($issueCode);

        if ($issueCode === '') {
            throw new InvalidArgumentException(
                'A repair resolver must have an issue code.',
            );
        }

        if (isset($this->resolvers[$issueCode])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Repair resolver [%s] is already registered.',
                    $issueCode,
                ),
            );
        }

        $this->resolvers[$issueCode] = $resolver;

        return $this;
    }

    public function registerProvider(
        RepairProviderContract $provider,
    ): self {
        foreach ($this->providers as $registeredProvider) {
            if (
                $registeredProvider::class
                === $provider::class
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Repair provider [%s] is already registered.',
                        $provider::class,
                    ),
                );
            }
        }

        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @param  iterable<int, RepairProviderContract>  $providers
     */
    public function registerProviders(
        iterable $providers,
    ): self {
        foreach ($providers as $provider) {
            if (! $provider instanceof RepairProviderContract) {
                throw new InvalidArgumentException(
                    'Repair providers must implement the repair provider contract.',
                );
            }

            $this->registerProvider($provider);
        }

        return $this;
    }

    public function has(
        string $issueCode,
    ): bool {
        $issueCode = trim($issueCode);

        if (isset($this->resolvers[$issueCode])) {
            return true;
        }

        foreach ($this->providers as $provider) {
            if (
                in_array(
                    $issueCode,
                    $provider->issueCodes(),
                    true,
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->resolvers);
    }

    public function providerCount(): int
    {
        return count($this->providers);
    }

    public function recommend(
        ValidationResult $validationResult,
    ): RepairResult {
        $result = new RepairResult;

        foreach ($validationResult->all() as $issue) {
            $recommendation = $this->resolve(
                $issue,
            );

            if ($recommendation === null) {
                continue;
            }

            $result->add($recommendation);
        }

        return $result;
    }

    public function clear(): self
    {
        $this->resolvers = [];
        $this->providers = [];

        return $this;
    }

    private function resolve(
        ValidationIssue $issue,
    ): ?RepairRecommendation {
        $resolver = $this->resolvers[
            $issue->code()
        ] ?? null;

        if ($resolver !== null) {
            return $resolver($issue);
        }

        foreach ($this->providers as $provider) {
            if (! $provider->supports($issue)) {
                continue;
            }

            $recommendation = $provider->recommend(
                $issue,
            );

            if ($recommendation !== null) {
                return $recommendation;
            }
        }

        return null;
    }
}