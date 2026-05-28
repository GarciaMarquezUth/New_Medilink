<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PortalCitaController extends Controller
{
    public function index(): View
    {
        return view('portal.citas');
    }
}
