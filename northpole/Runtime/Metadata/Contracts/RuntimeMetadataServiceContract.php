<?php

declare(strict_types=1);

namespace Northpole\Runtime\Metadata\Contracts;

interface RuntimeMetadataServiceContract
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function modules(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function module(string $slug): ?array;

    /**
     * @return array<string, array<string, string>>
     */
    public function commands(): array;

    /**
     * @return array<string, array<string, string>>
     */
    public function queries(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function events(): array;

    /**
     * @return array<string, array<int, string>>
     */
    public function permissions(): array;

    /**
     * @return array<string, array<int, string>>
     */
    public function capabilities(): array;

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function navigation(): array;

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function notifications(): array;

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function scheduledJobs(): array;

    /**
     * @return array<string, mixed>
     */
    public function graph(): array;
}