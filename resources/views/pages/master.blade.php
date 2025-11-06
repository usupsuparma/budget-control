@extends('layouts.master')

@section('title', 'Setting | Master')

@section('title-sub', 'Master')
@section('pagetitle', 'Setting')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

<!-- Begin page -->

<div class="col-12 col-lg-12">
    <div class="card card-h-100">
        <div class="card-header">
            <h5 class="card-title mb-0">Master</h5>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-3">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#employee" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="fas fa-home"></i></span>
                                <span>Employee</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#employment" role="tab" aria-selected="true">
                                <span><i class="far fa-user"></i></span>
                                <span>Employment</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#organization" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="far fa-envelope"></i></span>
                                <span>Organization</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_level" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="fas fa-cog"></i></span>
                                <span>Job Level</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_position" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="fas fa-cog"></i></span>
                                <span>Job Position</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#history" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="fas fa-cog"></i></span>
                                <span>History</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#authorization" role="tab" aria-selected="false" tabindex="-1">
                                <span><i class="fas fa-cog"></i></span>
                                <span>Authorization</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-9">
                    <!-- Tab panes -->
                    <div class="tab-content pt-3">
                        <div class="tab-pane" id="employee" role="tabpanel">
                            <h6 class="mt-1 mb-3">Employee</h6>
                            <p class="mb-0">
                                Welcome to our Admin! Here you can find information about our latest news, events, and updates. Stay tuned for blog posts and announcements. If you're new, we recommend checking out our About Us page to learn more about what we do and how we can help you. Feel free to explore and contact us if you have any questions!
                            </p>
                        </div>
                        <div class="tab-pane active show" id="employment" role="tabpanel">
                            <h6 class="mt-1 mb-3">Employment</h6>
                            <p class="mb-0">
                                Hello, John Doe! Here’s a brief overview of your profile. You’ve been a member since 2020 and have actively participated in various forums and discussions. Your recent activities include posting in the tech forum and commenting on the latest industry trends. Update your profile picture, manage your personal information, or review your recent activity logs to stay on top of things.
                            </p>
                        </div>
                        <div class="tab-pane" id="organization" role="tabpanel">
                            <h6 class="mt-1 mb-3">Organization</h6>
                            <p class="mb-0">
                                You have 3 new messages in your inbox. Check them out to stay updated with the latest communications from your team and friends. Don’t forget to review your message threads for any important announcements or updates. If you need to send a new message or reply to existing conversations, use the messaging tools provided here.
                            </p>
                        </div>
                        <div class="tab-pane" id="job_level" role="tabpanel">
                            <h6 class="mt-1 mb-3">Job Level</h6>
                            <p class="mb-0">
                                Customize your account settings here. Update your email address, change your password, or adjust your notification preferences. You can also configure privacy settings to control who can see your information. Make sure to review your settings periodically to ensure everything is tailored to your needs and preferences.
                            </p>
                        </div>
                        <div class="tab-pane" id="job_position" role="tabpanel">
                            <h6 class="mt-1 mb-3">Job Position</h6>
                            <p class="mb-0">
                                Customize your account settings here. Update your email address, change your password, or adjust your notification preferences. You can also configure privacy settings to control who can see your information. Make sure to review your settings periodically to ensure everything is tailored to your needs and preferences.
                            </p>
                        </div>
                        <div class="tab-pane" id="history" role="tabpanel">
                            <h6 class="mt-1 mb-3">History</h6>
                            <p class="mb-0">
                                Customize your account settings here. Update your email address, change your password, or adjust your notification preferences. You can also configure privacy settings to control who can see your information. Make sure to review your settings periodically to ensure everything is tailored to your needs and preferences.
                            </p>
                        </div>
                        <div class="tab-pane" id="authorization" role="tabpanel">
                            <h6 class="mt-1 mb-3">Authorization</h6>
                            <p class="mb-0">
                                Customize your account settings here. Update your email address, change your password, or adjust your notification preferences. You can also configure privacy settings to control who can see your information. Make sure to review your settings periodically to ensure everything is tailored to your needs and preferences.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!--End col-->

</main>
@endsection

@section('js')

<!-- App js -->
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>

<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

<script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>
@endsection