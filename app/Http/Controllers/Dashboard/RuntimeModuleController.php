<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RuntimeModuleController extends Controller
{
    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {}

    public function __invoke(string $slug): View
    {
        $module = $this->metadata->module($slug);

        if ($module === null) {
            throw new NotFoundHttpException(
                sprintf(
                    'Runtime module [%s] was not found.',
                    $slug,
                ),
            );
        }

        return view(
            'dashboard.module',
            [
                'module' => $module,
                'summary' => [
                    'dependencies' => count(
                        $module['dependencies'],
                    ),
                    'commands' => count(
                        $module['commands'],
                    ),
                    'queries' => count(
                        $module['queries'],
                    ),
                    'permissions' => count(
                        $module['permissions'],
                    ),
                    'capabilities' => count(
                        $module['capabilities'],
                    ),
                    'published_events' => count(
                        $module['published_events'],
                    ),
                    'event_subscribers' => array_sum(
                        array_map(
                            'count',
                            $module['event_subscribers'],
                        ),
                    ),
                    'notifications' => count(
                        $module['notifications'],
                    ),
                    'scheduled_jobs' => count(
                        $module['scheduled_jobs'],
                    ),
                ],
            ],
        );
    }
}
