<?php

declare(strict_types=1);

namespace Northpole\Runtime\Health;

final readonly class ModuleHealth
{
    /**
     * @param array<int, array{
     *     key: string,
     *     label: string,
     *     healthy: bool,
     *     message: string|null
     * }> $checks
     */
    public function __construct(
        private string $slug,
        private string $name,
        private string $status,
        private int $score,
        private array $checks,
    ) {}

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function score(): int
    {
        return $this->score;
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     healthy: bool,
     *     message: string|null
     * }>
     */
    public function checks(): array
    {
        return $this->checks;
    }

    /**
     * @return array{
     *     slug: string,
     *     name: string,
     *     status: string,
     *     score: int,
     *     checks: array<int, array{
     *         key: string,
     *         label: string,
     *         healthy: bool,
     *         message: string|null
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'status' => $this->status,
            'score' => $this->score,
            'checks' => $this->checks,
        ];
    }
}