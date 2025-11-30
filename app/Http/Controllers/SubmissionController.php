<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function user()
    {
        $title = 'Submission Users';
        return view('pages.submission.user', compact('title'));
    }

    public function user_create()
    {
        $title = 'Submission Users Create';
        return view('pages.submission.user_create', compact('title'));
    }

    public function admin()
    {
        $title = 'Submission Admin';
        return view('pages.submission.admin', compact('title'));
    }
}
