<?php

namespace Modules\HomeDoctor\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class HomeDoctorController extends Controller
{
    public function index(): View
    {
        return view('home-doctor::index');
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'module' => 'HomeDoctor',
            'status' => 'active',
            'version' => '0.1.0',
        ]);
    }
}