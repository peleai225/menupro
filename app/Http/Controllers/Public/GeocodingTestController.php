<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GeocodingTestController extends Controller
{
    /**
     * Display the geocoding test page
     */
    public function index(): View
    {
        return view('pages.public.geocoding-test');
    }
}
