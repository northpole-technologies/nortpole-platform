<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

use InvalidArgumentException;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationSeverity;

final class RepairRecommendation
{
    /**
     * @var array<int, RepairAction>
     */
    private array $actions = [];

    /**
     * @param  iterable<int, RepairAction>  $actions
     */
    public function __construct(
        private readonly string $code,
        private readonly string $title,
        private readonly string $description,
        private readonly ValidationSeverity $severity,
        private readonly ?string $module = null,
        private readonly ?ValidationIssue $issue = null,
        iterable $actions = [],
    ) {
        if (trim($this->code) === '') {
            throw new InvalidArgumentException(
                'A repair recommendation must have a code.',
            );
        }

        if (trim($this->title) === '') {
            throw new InvalidArgumentException(
                'A repair recommendation must have a title.',
            );
        }

        if (trim($this->description) === '') {
            throw new InvalidArgumentException(
                'A repair recommendation must have a description.',
            );
        }

        if (
            $this->module !== null
            && trim($this->module) === ''
        ) {
            throw new InvalidArgumentException(
                'A repair recommendation module cannot be empty.',
            );
        }

        $this->addMany($actions);
    }

    public function code(): string
    {
        return trim($this->code);
    }

    public function title(): string
    {
        return trim($this->title);
    }

    public function description(): string
    {
        return trim($this->description);
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

    public function issue(): ?ValidationIssue
    {
        return $this->issue;
    }

    public function add(
        RepairAction $action,
    ): self {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @param  iterable<int, RepairAction>  $actions
     */
    public function addMany(
        iterable $actions,
    ): self {
        foreach ($actions as $action) {
            if (! $action instanceof RepairAction) {
                throw new InvalidArgumentException(
                    'Repair recommendations may only contain repair actions.',
                );
            }

            $this->add($action);
        }

        return $this;
    }

    /**
     * @return array<int, RepairAction>
     */
    public function actions(): array
    {
        return $this->actions;
    }

    /**
     * @return array{
     *     code: string,
     *     title: string,
     *     description: string,
     *     severity: string,
     *     module: string|null,
     *     actions: array<int, array{
     *         type: string,
     *         label: string,
     *         content: string
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code(),
            'title' => $this->title(),
            'description' => $this->description(),
            'severity' => $this->severity->value,
            'module' => $this->module(),
            'actions' => array_map(
                static fn (
                    RepairAction $action,
                ): array => $action->toArray(),
                $this->actions,
            ),
        ];
    }
}
