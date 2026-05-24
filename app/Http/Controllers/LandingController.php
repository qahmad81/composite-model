<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\SitePage;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $menuPages = SitePage::where('show_in_menu', true)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        return view('landing', compact('settings', 'menuPages'));
    }
}
