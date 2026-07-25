<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Metadata\RuntimeMetadataService;

final class RuntimeGraphController extends Controller
{
    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {}

    public function __invoke(): View
    {
        $graph = $this->metadata->graph();

        return view(
            'dashboard.graph',
            [
                'nodes' => $graph['nodes'],
                'edges' => $graph['edges'],
                'summary' => [
                    'modules' => count(
                        $graph['nodes'],
                    ),
                    'dependencies' => count(
                        $graph['edges'],
                    ),
                    'enabled' => count(
                        array_filter(
                            $graph['nodes'],
                            static fn (array $node): bool => $node['enabled'],
                        ),
                    ),
                ],
            ],
        );
    }
}
