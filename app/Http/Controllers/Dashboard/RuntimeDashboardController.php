<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Diagnostics\RuntimeDashboardViewModel;

final class RuntimeDashboardController extends Controller
{
    public function __construct(
        private readonly RuntimeDashboardViewModel $viewModel,
    ) {}

    public function __invoke(): View
    {
        return view(
            'dashboard.runtime',
            $this->viewModel->data(),
        );
    }
}