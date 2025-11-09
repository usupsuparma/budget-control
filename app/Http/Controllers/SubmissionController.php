<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function user()
    {
        $title = 'Submission Users';
        return view('pages.Submission_user', compact('title'));
    }

    public function admin()
    {
        $title = 'Submission Admin';
        return view('pages.Submission_admin', compact('title'));
    }
}
