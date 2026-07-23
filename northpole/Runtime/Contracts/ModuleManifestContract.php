<?php

namespace Northpole\Runtime\Contracts;

interface ModuleManifestContract
{
    public function name(): string;

    public function slug(): string;

    public function version(): string;

    public function description(): ?string;

    public function provider(): ?string;

    public function enabled(): bool;

    public function path(): string;

    public function manifestPath(): string;

    /**
     * @return array<string, string>
     */
    public function routes(): array;

    public function viewsPath(): ?string;

    public function migrationsPath(): ?string;

    /**
     * @return array<string, string>
     */
    public function configuration(): array;

    /**
     * @return array<int, string>
     */
    public function permissions(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function navigation(): array;

    /**
     * @return array<int, string>
     */
    public function capabilities(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}