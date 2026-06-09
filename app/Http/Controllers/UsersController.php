<?php

namespace App\Http\Controllers;

use App\Services\UserSettingsService\UserSettingsService;

class UsersController extends Controller
{
    public function __construct(
        private readonly UserSettingsService $userSettingsService,
    ) {
    }

    public function index()
    {
        $title = "Users Data";
        $data = $this->userSettingsService->getPageData();

        return view('pages.settings.users', array_merge(compact('title'), $data));
    }
}
