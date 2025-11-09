<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Dashboard';
        return view('pages.dashboard', compact('title'));
    }
    public function executive(Request $request)
    {
        $title = 'Dashboard Executive';
        return view('pages.dash-executive', compact('title'));
    }
}
