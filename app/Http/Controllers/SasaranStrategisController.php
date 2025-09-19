<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SasaranStrategisController extends Controller
{
    public function index()
    {

        $title = 'Sasaran Strategis';
        return view('pages.SasaranStrategis', compact('title'));
    }
}
