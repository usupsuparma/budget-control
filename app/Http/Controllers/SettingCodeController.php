<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingCodeController extends Controller
{
    public function index()
    {
        $title = "Code Data";

        return view('pages.settings.SettingCode', compact('title'));
    }
}
