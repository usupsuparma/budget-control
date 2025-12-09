<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingProductionController extends Controller
{
    public function index()
    {
        $title = 'Production Settings';

        // dd($employee);


        return view('pages.settings.settingProduction', compact('title'));
    }
}
