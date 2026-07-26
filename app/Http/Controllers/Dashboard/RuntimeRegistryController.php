<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RuntimeRegistryController extends Controller
{
    /**
     * @var array<string, array{
     *     title: string,
     *     description: string,
     *     method: string
     * }>
     */
    private const REGISTRIES = [
        'commands' => [
            'title' => 'Commands',
            'description' =>
                'Runtime commands grouped by their contributing module.',
            'method' => 'commands',
        ],
        'queries' => [
            'title' => 'Queries',
            'description' =>
                'Runtime queries grouped by their contributing module.',
            'method' => 'queries',
        ],
        'agents' => [
            'title' => 'Agents',
            'description' =>
                'Runtime agents grouped by their contributing module.',
            'method' => 'agents',
        ],
        'events' => [
            'title' => 'Events',
            'description' =>
                'Published events and registered subscribers by module.',
            'method' => 'events',
        ],
        'permissions' => [
            'title' => 'Permissions',
            'description' =>
                'Permissions contributed by runtime modules.',
            'method' => 'permissions',
        ],
        'capabilities' => [
            'title' => 'Capabilities',
            'description' =>
                'Platform capabilities exposed by runtime modules.',
            'method' => 'capabilities',
        ],
        'navigation' => [
            'title' => 'Navigation',
            'description' =>
                'Navigation entries registered by runtime modules.',
            'method' => 'navigation',
        ],
        'notifications' => [
            'title' => 'Notifications',
            'description' =>
                'Notification definitions registered by runtime modules.',
            'method' => 'notifications',
        ],
        'scheduled-jobs' => [
            'title' => 'Scheduled Jobs',
            'description' =>
                'Recurring work declared by runtime modules.',
            'method' => 'scheduledJobs',
        ],
    ];

    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {
    }

    public function __invoke(string $registry): View
    {
        $definition = self::REGISTRIES[$registry] ?? null;

        if ($definition === null) {
            throw new NotFoundHttpException(
                sprintf(
                    'Runtime registry [%s] was not found.',
                    $registry,
                ),
            );
        }

        $method = $definition['method'];
        $groups = $this->metadata->{$method}();

        return view(
            'dashboard.registry',
            [
                'registry' => $registry,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'groups' => $groups,
                'summary' => [
                    'modules' => count($groups),
                    'items' => $this->countItems(
                        $registry,
                        $groups,
                    ),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed> $groups
     */
    private function countItems(
        string $registry,
        array $groups,
    ): int {
        if ($registry !== 'events') {
            return array_sum(
                array_map(
                    static fn (mixed $items): int =>
                        is_array($items)
                            ? count($items)
                            : 0,
                    $groups,
                ),
            );
        }

        $count = 0;

        foreach ($groups as $metadata) {
            if (! is_array($metadata)) {
                continue;
            }

            $count += count(
                $metadata['publishes'] ?? [],
            );

            foreach (
                $metadata['subscribes'] ?? []
                as $listeners
            ) {
                if (is_array($listeners)) {
                    $count += count($listeners);
                }
            }
        }

        return $count;
    }
}