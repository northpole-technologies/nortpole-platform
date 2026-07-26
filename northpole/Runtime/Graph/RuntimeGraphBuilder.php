<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class RuntimeGraphBuilder implements RuntimeGraphBuilderContract
{
    public function __construct(
        private readonly Runtime $runtime,
    ) {}

    public function build(): RuntimeGraph
    {
        $graph = new RuntimeGraph;

        $this->addModules(
            $graph,
        );

        $this->addDependencies(
            $graph,
        );

        $this->addHandledRegistrations(
            $graph,
            registry: 'commands',
            type: 'command',
        );

        $this->addHandledRegistrations(
            $graph,
            registry: 'queries',
            type: 'query',
        );

        $this->addHandledRegistrations(
            $graph,
            registry: 'agents',
            type: 'agent',
        );

        $this->addEvents(
            $graph,
        );

        return $graph;
    }

    private function addModules(
        RuntimeGraph $graph,
    ): void {
        foreach ($this->runtime->modules() as $module) {
            $graph->addNode(
                new RuntimeGraphNode(
                    id: $this->moduleNodeId(
                        $module->slug(),
                    ),
                    type: 'module',
                    label: $module->name(),
                    module: $module->slug(),
                    metadata: [
                        'slug' => $module->slug(),
                        'version' => $module->version(),
                        'enabled' => $module->enabled(),
                        'description' => $module->description(),
                        'inspector' => [
                            'type' => 'module',
                            'module' => $module->slug(),
                        ],
                    ],
                ),
            );
        }
    }

    private function addDependencies(
        RuntimeGraph $graph,
    ): void {
        foreach ($this->runtime->modules() as $module) {
            foreach (
                $module->dependencyConstraints() as $dependency => $constraint
            ) {
                $targetNodeId = $this->moduleNodeId(
                    $dependency,
                );

                if (! $graph->hasNode($targetNodeId)) {
                    $graph->addNode(
                        new RuntimeGraphNode(
                            id: $targetNodeId,
                            type: 'module',
                            label: $dependency,
                            module: $dependency,
                            metadata: [
                                'slug' => $dependency,
                                'enabled' => false,
                                'discovered' => false,
                            ],
                        ),
                    );
                }

                $graph->addEdge(
                    new RuntimeGraphEdge(
                        source: $this->moduleNodeId(
                            $module->slug(),
                        ),
                        target: $targetNodeId,
                        type: 'depends_on',
                        label: 'Depends on',
                        metadata: [
                            'constraint' => $constraint,
                        ],
                    ),
                );
            }
        }
    }

    private function addHandledRegistrations(
        RuntimeGraph $graph,
        string $registry,
        string $type,
    ): void {
        foreach ($this->runtime->modules() as $module) {
            $registrations = match ($registry) {
                'commands' => $module->handledCommands(),
                'queries' => $module->handledQueries(),
                'agents' => $module->handledAgents(),
                default => [],
            };

            foreach ($registrations as $key => $handler) {
                $registrationNodeId = $this->registrationNodeId(
                    type: $type,
                    module: $module->slug(),
                    key: $key,
                );

                $handlerNodeId = $this->classNodeId(
                    prefix: $type.'_handler',
                    class: $handler,
                );

                $graph->addNode(
                    new RuntimeGraphNode(
                        id: $registrationNodeId,
                        type: $type,
                        label: $key,
                        module: $module->slug(),
                        metadata: [
                            'registry' => $registry,
                            'key' => $key,
                            'handler' => $handler,
                            'inspector' => [
                                'registry' => $registry,
                                'module' => $module->slug(),
                                'key' => $key,
                            ],
                        ],
                    ),
                );

                $graph->addNode(
                    new RuntimeGraphNode(
                        id: $handlerNodeId,
                        type: $type.'_handler',
                        label: $this->classBasename(
                            $handler,
                        ),
                        module: $module->slug(),
                        metadata: [
                            'class' => $handler,
                        ],
                    ),
                );

                $graph->addEdge(
                    new RuntimeGraphEdge(
                        source: $this->moduleNodeId(
                            $module->slug(),
                        ),
                        target: $registrationNodeId,
                        type: 'declares',
                        label: 'Declares',
                        metadata: [
                            'registry' => $registry,
                        ],
                    ),
                );

                $graph->addEdge(
                    new RuntimeGraphEdge(
                        source: $registrationNodeId,
                        target: $handlerNodeId,
                        type: 'handled_by',
                        label: 'Handled by',
                    ),
                );
            }
        }
    }

    private function addEvents(
        RuntimeGraph $graph,
    ): void {
        foreach ($this->runtime->modules() as $module) {
            $this->addPublishedEvents(
                $graph,
                $module,
            );

            $this->addEventSubscribers(
                $graph,
                $module,
            );
        }
    }

    private function addPublishedEvents(
        RuntimeGraph $graph,
        ModuleManifest $module,
    ): void {
        foreach ($module->publishedEvents() as $event) {
            $eventNodeId = $this->eventNodeId(
                $event,
            );

            $this->addEventNode(
                graph: $graph,
                eventNodeId: $eventNodeId,
                event: $event,
                module: $module->slug(),
            );

            $graph->addEdge(
                new RuntimeGraphEdge(
                    source: $this->moduleNodeId(
                        $module->slug(),
                    ),
                    target: $eventNodeId,
                    type: 'publishes',
                    label: 'Publishes',
                ),
            );
        }
    }

    private function addEventSubscribers(
        RuntimeGraph $graph,
        ModuleManifest $module,
    ): void {
        foreach (
            $module->eventSubscribers() as $event => $listeners
        ) {
            $eventNodeId = $this->eventNodeId(
                $event,
            );

            $this->addEventNode(
                graph: $graph,
                eventNodeId: $eventNodeId,
                event: $event,
                module: $module->slug(),
            );

            foreach ($listeners as $listener) {
                $listenerNodeId = $this->classNodeId(
                    prefix: 'event_listener',
                    class: $listener,
                );

                $graph->addNode(
                    new RuntimeGraphNode(
                        id: $listenerNodeId,
                        type: 'event_listener',
                        label: $this->classBasename(
                            $listener,
                        ),
                        module: $module->slug(),
                        metadata: [
                            'class' => $listener,
                            'event' => $event,
                        ],
                    ),
                );

                $graph->addEdge(
                    new RuntimeGraphEdge(
                        source: $eventNodeId,
                        target: $listenerNodeId,
                        type: 'subscribed_by',
                        label: 'Subscribed by',
                    ),
                );

                $graph->addEdge(
                    new RuntimeGraphEdge(
                        source: $this->moduleNodeId(
                            $module->slug(),
                        ),
                        target: $listenerNodeId,
                        type: 'registers_listener',
                        label: 'Registers listener',
                    ),
                );
            }
        }
    }

    private function addEventNode(
        RuntimeGraph $graph,
        string $eventNodeId,
        string $event,
        string $module,
    ): void {
        if ($graph->hasNode($eventNodeId)) {
            return;
        }

        $graph->addNode(
            new RuntimeGraphNode(
                id: $eventNodeId,
                type: 'event',
                label: $event,
                module: $module,
                metadata: [
                    'registry' => 'events',
                    'key' => $event,
                    'inspector' => [
                        'registry' => 'events',
                        'module' => $module,
                        'key' => $event,
                    ],
                ],
            ),
        );
    }

    private function moduleNodeId(
        string $module,
    ): string {
        return 'module:'.strtolower(
            trim($module),
        );
    }

    private function registrationNodeId(
        string $type,
        string $module,
        string $key,
    ): string {
        return sprintf(
            '%s:%s:%s',
            trim($type),
            strtolower(
                trim($module),
            ),
            trim($key),
        );
    }

    private function eventNodeId(
        string $event,
    ): string {
        return 'event:'.$this->hash(
            trim($event),
        );
    }

    private function classNodeId(
        string $prefix,
        string $class,
    ): string {
        return trim($prefix).':'.$this->hash(
            trim($class),
        );
    }

    private function hash(
        string $value,
    ): string {
        return substr(
            hash(
                'sha256',
                $value,
            ),
            0,
            16,
        );
    }

    private function classBasename(
        string $class,
    ): string {
        $position = strrpos(
            $class,
            '\\',
        );

        if ($position === false) {
            return $class;
        }

        return substr(
            $class,
            $position + 1,
        );
    }
}
