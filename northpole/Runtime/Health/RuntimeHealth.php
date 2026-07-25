<?php

declare(strict_types=1);

namespace Northpole\Runtime\Health;

final readonly class RuntimeHealth
{
    /**
     * @param array<int, ModuleHealth> $modules
     */
    public function __construct(
        private string $status,
        private int $score,
        private array $modules,
    ) {}

    public function status(): string
    {
        return $this->status;
    }

    public function score(): int
    {
        return $this->score;
    }

    /**
     * @return array<int, ModuleHealth>
     */
    public function modules(): array
    {
        return $this->modules;
    }

    /**
     * @return array{
     *     status: string,
     *     score: int,
     *     modules: array<int, array{
     *         slug: string,
     *         name: string,
     *         status: string,
     *         score: int,
     *         checks: array<int, array{
     *             key: string,
     *             label: string,
     *             healthy: bool,
     *             message: string|null
     *         }>
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'score' => $this->score,
            'modules' => array_map(
                static fn (ModuleHealth $module): array =>
                    $module->toArray(),
                $this->modules,
            ),
        ];
    }
}