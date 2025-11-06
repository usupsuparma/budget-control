<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $title = 'Master Data';

        $employee = Employee::where('status', 1)->get();


        return view('pages.master', compact('title', 'employee'));
    }
}
