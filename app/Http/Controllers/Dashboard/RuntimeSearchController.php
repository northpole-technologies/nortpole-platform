<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Northpole\Runtime\Search\RuntimeSearchViewModel;

final class RuntimeSearchController extends Controller
{
    public function __construct(
        private readonly RuntimeSearchViewModel $viewModel,
    ) {}

    public function __invoke(Request $request): View
    {
        return view(
            'dashboard.search',
            $this->viewModel->data(
                $request->string('q')->toString(),
                $request->string('module')->toString(),
            ),
        );
    }
}