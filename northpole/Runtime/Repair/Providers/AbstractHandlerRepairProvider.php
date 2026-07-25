<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair\Providers;

use Northpole\Runtime\Repair\Contracts\RepairProviderContract;
use Northpole\Runtime\Repair\RepairAction;
use Northpole\Runtime\Repair\RepairActionType;
use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Validation\ValidationIssue;

abstract class AbstractHandlerRepairProvider implements
    RepairProviderContract
{
    /**
     * @return array<int, string>
     */
    final public function issueCodes(): array
    {
        return [
            $this->code('handler_class_missing'),
            $this->code('handler_contract_invalid'),
            $this->code('handler_unregistered'),
            $this->code('handler_mismatch'),
            $this->code('owner_mismatch'),
            $this->code('handler_unexpected'),
            $this->code('declaration_duplicate'),
        ];
    }

    final public function supports(
        ValidationIssue $issue,
    ): bool {
        return in_array(
            $issue->code(),
            $this->issueCodes(),
            true,
        );
    }

    final public function recommend(
        ValidationIssue $issue,
    ): ?RepairRecommendation {
        if (! $this->supports($issue)) {
            return null;
        }

        return match ($issue->code()) {
            $this->code('handler_class_missing') =>
                $this->missingHandler($issue),

            $this->code('handler_contract_invalid') =>
                $this->invalidContract($issue),

            $this->code('handler_unregistered') =>
                $this->unregisteredHandler($issue),

            $this->code('handler_mismatch') =>
                $this->handlerMismatch($issue),

            $this->code('owner_mismatch') =>
                $this->ownerMismatch($issue),

            $this->code('handler_unexpected') =>
                $this->unexpectedHandler($issue),

            $this->code('declaration_duplicate') =>
                $this->duplicateDeclaration($issue),

            default => null,
        };
    }

    abstract protected function type(): string;

    abstract protected function displayName(): string;

    abstract protected function contract(): string;

    private function missingHandler(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $handler = $this->contextString(
            $context,
            'handler',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'create_missing_handler',
            ),
            title: sprintf(
                'Create missing %s handler',
                $this->displayName(),
            ),
            description: sprintf(
                'Create handler class [%s] for %s [%s] and ensure it is autoloadable.',
                $handler,
                $this->displayName(),
                $name,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Create the handler class',
                    content: sprintf(
                        'Create [%s] and implement [%s].',
                        $handler,
                        $this->contract(),
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Verify the manifest declaration',
                    content: sprintf(
                        'Confirm [%s] maps to [%s] in the module manifest.',
                        $name,
                        $handler,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function invalidContract(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $handler = $this->contextString(
            $context,
            'handler',
            'unknown',
        );

        $contract = $this->contextString(
            $context,
            'contract',
            $this->contract(),
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'implement_handler_contract',
            ),
            title: sprintf(
                'Correct %s handler contract',
                $this->displayName(),
            ),
            description: sprintf(
                'Handler [%s] for %s [%s] must implement [%s].',
                $handler,
                $this->displayName(),
                $name,
                $contract,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Implement the required contract',
                    content: sprintf(
                        'Update [%s] so it implements [%s] and provides the required handle method.',
                        $handler,
                        $contract,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function unregisteredHandler(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $handler = $this->contextString(
            $context,
            'expected_handler',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'register_handler',
            ),
            title: sprintf(
                'Register missing %s handler',
                $this->displayName(),
            ),
            description: sprintf(
                '%s [%s] is declared but handler [%s] was not registered during runtime boot.',
                ucfirst($this->displayName()),
                $name,
                $handler,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Review the module manifest',
                    content: sprintf(
                        'Confirm the manifest declares [%s] with handler [%s].',
                        $name,
                        $handler,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Review the runtime stage',
                    content: sprintf(
                        'Confirm the %s handler stage runs for module [%s].',
                        $this->displayName(),
                        $issue->module() ?? 'unknown',
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function handlerMismatch(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $expected = $this->contextString(
            $context,
            'expected_handler',
            'unknown',
        );

        $registered = $this->contextString(
            $context,
            'registered_handler',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'align_handler_registration',
            ),
            title: sprintf(
                'Align %s handler registration',
                $this->displayName(),
            ),
            description: sprintf(
                '%s [%s] expects handler [%s], but [%s] is registered.',
                ucfirst($this->displayName()),
                $name,
                $expected,
                $registered,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Select the authoritative handler',
                    content: sprintf(
                        'Decide whether [%s] or [%s] is correct for [%s].',
                        $expected,
                        $registered,
                        $name,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Align manifest and registry',
                    content: sprintf(
                        'Update the declaration or registration so [%s] resolves to one handler.',
                        $name,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function ownerMismatch(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $expectedOwner = $this->contextString(
            $context,
            'expected_owner',
            'unknown',
        );

        $registeredOwner = $this->contextString(
            $context,
            'registered_owner',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'correct_handler_owner',
            ),
            title: sprintf(
                'Correct %s ownership',
                $this->displayName(),
            ),
            description: sprintf(
                '%s [%s] belongs to module [%s], but the registry owner is [%s].',
                ucfirst($this->displayName()),
                $name,
                $expectedOwner,
                $registeredOwner,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Correct the registry owner',
                    content: sprintf(
                        'Register [%s] with module owner [%s].',
                        $name,
                        $expectedOwner,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Check duplicate declarations',
                    content: sprintf(
                        'Confirm module [%s] is the only owner of [%s].',
                        $expectedOwner,
                        $name,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function unexpectedHandler(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $handler = $this->contextString(
            $context,
            'handler',
            'unknown',
        );

        $owner = $this->contextString(
            $context,
            'owner',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'resolve_unexpected_handler',
            ),
            title: sprintf(
                'Resolve unexpected %s registration',
                $this->displayName(),
            ),
            description: sprintf(
                'Registry entry [%s] uses handler [%s] and owner [%s], but no module manifest declares it.',
                $name,
                $handler,
                $owner,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Declare or remove the registration',
                    content: sprintf(
                        'Add [%s] to the owning module manifest or remove its stale registry registration.',
                        $name,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function duplicateDeclaration(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $name = $this->contextString(
            $context,
            'name',
            'unknown',
        );

        $firstModule = $this->contextString(
            $context,
            'first_module',
            'unknown',
        );

        $secondModule = $this->contextString(
            $context,
            'second_module',
            'unknown',
        );

        return new RepairRecommendation(
            code: $this->repairCode(
                'resolve_duplicate_declaration',
            ),
            title: sprintf(
                'Resolve duplicate %s declaration',
                $this->displayName(),
            ),
            description: sprintf(
                '%s [%s] is declared by both module [%s] and module [%s].',
                ucfirst($this->displayName()),
                $name,
                $firstModule,
                $secondModule,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Choose one owning module',
                    content: sprintf(
                        'Keep [%s] in either module [%s] or module [%s], but not both.',
                        $name,
                        $firstModule,
                        $secondModule,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function validationAction(): RepairAction
    {
        return new RepairAction(
            type: RepairActionType::PowerShell,
            label: 'Re-run runtime validation tests',
            content: 'php artisan test .\tests\Unit\Runtime\Validation .\tests\Unit\Runtime\Repair',
        );
    }

    private function code(
        string $suffix,
    ): string {
        return sprintf(
            '%s.%s',
            $this->type(),
            $suffix,
        );
    }

    private function repairCode(
        string $suffix,
    ): string {
        return sprintf(
            'repair.%s.%s',
            $this->type(),
            $suffix,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function contextString(
        array $context,
        string $key,
        string $fallback,
    ): string {
        $value = $context[$key] ?? null;

        if (! is_string($value)) {
            return $fallback;
        }

        $value = trim($value);

        return $value === ''
            ? $fallback
            : $value;
    }
}