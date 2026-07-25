<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Rules;

use Closure;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Validation\Contracts\ValidationRuleContract;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;

abstract class AbstractRegistryValidationRule implements
    ValidationRuleContract
{
    /**
     * @param  Closure(): array<string, ModuleManifestContract>  $modulesResolver
     */
    public function __construct(
        private readonly Closure $modulesResolver,
    ) {}

    final public function validate(): ValidationResult
    {
        $result = new ValidationResult;
        $declarations = [];

        foreach ($this->modules() as $module) {
            $moduleSlug = trim(
                $module->slug(),
            );

            foreach (
                $this->declarations($module)
                as $name => $handler
            ) {
                $name = trim($name);
                $handler = trim($handler);

                if (isset($declarations[$name])) {
                    $result->add(
                        new ValidationIssue(
                            code: sprintf(
                                '%s.declaration_duplicate',
                                $this->type(),
                            ),
                            message: sprintf(
                                '%s [%s] is declared by both module [%s] and module [%s].',
                                ucfirst($this->type()),
                                $name,
                                $declarations[$name]['module'],
                                $moduleSlug,
                            ),
                            severity: ValidationSeverity::Error,
                            module: $moduleSlug,
                            context: [
                                'name' => $name,
                                'first_module' =>
                                    $declarations[$name]['module'],
                                'second_module' => $moduleSlug,
                            ],
                        ),
                    );

                    continue;
                }

                $declarations[$name] = [
                    'handler' => $handler,
                    'module' => $moduleSlug,
                ];

                $this->validateDeclaration(
                    result: $result,
                    name: $name,
                    handler: $handler,
                    module: $moduleSlug,
                );
            }
        }

        $this->validateUnexpectedRegistrations(
            $result,
            $declarations,
        );

        return $result;
    }

    /**
     * @return array<string, ModuleManifestContract>
     */
    private function modules(): array
    {
        $modules = ($this->modulesResolver)();

        return $modules;
    }

    private function validateDeclaration(
        ValidationResult $result,
        string $name,
        string $handler,
        string $module,
    ): void {
        if (! class_exists($handler)) {
            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.handler_class_missing',
                        $this->type(),
                    ),
                    message: sprintf(
                        '%s handler class [%s] declared for [%s] does not exist.',
                        ucfirst($this->type()),
                        $handler,
                        $name,
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'name' => $name,
                        'handler' => $handler,
                    ],
                ),
            );
        } elseif (
            ! is_subclass_of(
                $handler,
                $this->handlerContract(),
            )
        ) {
            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.handler_contract_invalid',
                        $this->type(),
                    ),
                    message: sprintf(
                        '%s handler [%s] for [%s] must implement [%s].',
                        ucfirst($this->type()),
                        $handler,
                        $name,
                        $this->handlerContract(),
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'name' => $name,
                        'handler' => $handler,
                        'contract' => $this->handlerContract(),
                    ],
                ),
            );
        }

        $registeredHandler = $this->registeredHandler(
            $name,
        );

        if ($registeredHandler === null) {
            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.handler_unregistered',
                        $this->type(),
                    ),
                    message: sprintf(
                        '%s [%s] declared by module [%s] has no registered handler.',
                        ucfirst($this->type()),
                        $name,
                        $module,
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'name' => $name,
                        'expected_handler' => $handler,
                    ],
                ),
            );

            return;
        }

        if ($registeredHandler !== $handler) {
            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.handler_mismatch',
                        $this->type(),
                    ),
                    message: sprintf(
                        '%s [%s] declares handler [%s] but registry contains [%s].',
                        ucfirst($this->type()),
                        $name,
                        $handler,
                        $registeredHandler,
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'name' => $name,
                        'expected_handler' => $handler,
                        'registered_handler' => $registeredHandler,
                    ],
                ),
            );
        }

        $registeredOwner = $this->registeredOwner(
            $name,
        );

        if ($registeredOwner !== $module) {
            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.owner_mismatch',
                        $this->type(),
                    ),
                    message: sprintf(
                        '%s [%s] is declared by module [%s] but registered to module [%s].',
                        ucfirst($this->type()),
                        $name,
                        $module,
                        $registeredOwner ?? 'unknown',
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'name' => $name,
                        'expected_owner' => $module,
                        'registered_owner' => $registeredOwner,
                    ],
                ),
            );
        }
    }

    /**
     * @param  array<string, array{
     *     handler: string,
     *     module: string
     * }>  $declarations
     */
    private function validateUnexpectedRegistrations(
        ValidationResult $result,
        array $declarations,
    ): void {
        foreach (
            $this->registeredHandlers()
            as $name => $handler
        ) {
            if (isset($declarations[$name])) {
                continue;
            }

            $owner = $this->registeredOwner($name);

            $result->add(
                new ValidationIssue(
                    code: sprintf(
                        '%s.handler_unexpected',
                        $this->type(),
                    ),
                    message: sprintf(
                        'Registered %s [%s] with handler [%s] is not declared by any module manifest.',
                        $this->type(),
                        $name,
                        $handler,
                    ),
                    severity: ValidationSeverity::Warning,
                    module: $owner,
                    context: [
                        'name' => $name,
                        'handler' => $handler,
                        'owner' => $owner,
                    ],
                ),
            );
        }
    }

    /**
     * @return array<string, string>
     */
    abstract protected function declarations(
        ModuleManifestContract $module,
    ): array;

    abstract protected function type(): string;

    abstract protected function handlerContract(): string;

    abstract protected function registeredHandler(
        string $name,
    ): ?string;

    abstract protected function registeredOwner(
        string $name,
    ): ?string;

    /**
     * @return array<string, string>
     */
    abstract protected function registeredHandlers(): array;
}