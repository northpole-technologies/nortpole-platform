<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspector\Contracts\RuntimeInspectorServiceContract;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RuntimeRegistrationInspectorController extends Controller
{
    public function __construct(
        private readonly RuntimeInspectorServiceContract $inspector,
    ) {}

    public function __invoke(
        string $registry,
        string $module,
        string $key,
    ): View {
        $result = $this->inspector->inspect(
            new RuntimeInspectionReference(
                registry: $registry,
                module: $module,
                key: $key,
            ),
        );

        if (! $result->found()) {
            throw new NotFoundHttpException(
                sprintf(
                    'Runtime registration [%s:%s:%s] was not found.',
                    $registry,
                    $module,
                    $key,
                ),
            );
        }

        return view(
            'dashboard.inspector',
            [
                'result' => $result,
                'inspection' => $result->toArray(),
            ],
        );
    }
}
