<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $title = 'Approval';
        $employees = Employee::orderBy('first_name')->get();
        return view('pages.approval', compact('title', 'employees'));
    }
}
