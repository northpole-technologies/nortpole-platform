<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsViewModel;

final class RuntimeDiagnosticsController extends Controller
{
    public function __construct(
        private readonly RuntimeDiagnosticsViewModel $viewModel,
    ) {}

    public function __invoke(): View
    {
        return view(
            'dashboard.diagnostics',
            $this->viewModel->data(),
        );
    }
}