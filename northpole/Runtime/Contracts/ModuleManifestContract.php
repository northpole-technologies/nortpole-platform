<?php

declare(strict_types=1);

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
     * Returns dependency module slugs.
     *
     * @return array<int, string>
     */
    public function dependencies(): array;

    /**
     * Returns dependency module slugs mapped to their version constraints.
     *
     * Legacy dependencies without an explicit constraint use [*].
     *
     * @return array<string, string>
     */
    public function dependencyConstraints(): array;

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
     * Returns the event names this module may publish.
     *
     * @return array<int, string>
     */
    public function publishedEvents(): array;

    /**
     * Returns subscribed event names mapped to listener classes.
     *
     * @return array<string, array<int, string>>
     */
    public function eventSubscribers(): array;

    /**
     * Returns command names mapped to the handler classes owned by this module.
     *
     * @return array<string, string>
     */
    public function handledCommands(): array;

    /**
     * Returns query names mapped to the handler classes owned by this module.
     *
     * @return array<string, string>
     */
    public function handledQueries(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
