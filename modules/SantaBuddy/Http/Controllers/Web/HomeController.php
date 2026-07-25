<?php

namespace Modules\SantaBuddy\Http\Controllers\Web;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('santa-buddy::welcome');
    }
}
