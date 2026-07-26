<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Rules\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use Northpole\Runtime\Validation\Contracts\ValidationRuleContract;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;

final class OrphanNodeValidationRule implements ValidationRuleContract
{
    public function __construct(
        private readonly RuntimeGraphBuilderContract $graphBuilder,
    ) {}

    public function name(): string
    {
        return 'graph-orphan-nodes';
    }

    public function validate(): ValidationResult
    {
        $graph = $this->graphBuilder->build();
        $connectedNodes = [];

        foreach ($graph->edges() as $edge) {
            $connectedNodes[$edge->source] = true;
            $connectedNodes[$edge->target] = true;
        }

        $result = new ValidationResult;

        foreach ($graph->nodes() as $node) {
            if ($this->shouldIgnore($node)) {
                continue;
            }

            if (isset($connectedNodes[$node->id])) {
                continue;
            }

            $result->add(
                new ValidationIssue(
                    code: 'graph.node_orphaned',
                    message: sprintf(
                        'Runtime graph %s node [%s] is not connected to any other runtime component.',
                        $node->type,
                        $node->label,
                    ),
                    severity: ValidationSeverity::Warning,
                    module: $node->module,
                    context: [
                        'node_id' => $node->id,
                        'node_type' => $node->type,
                        'node_label' => $node->label,
                    ],
                ),
            );
        }

        return $result;
    }

    private function shouldIgnore(
        RuntimeGraphNode $node,
    ): bool {
        return $node->type === 'module';
    }
}
