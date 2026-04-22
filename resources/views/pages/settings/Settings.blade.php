@extends('layouts.master')

@section('title', 'Setting | Master')
@section('title-sub', 'Master')
@section('pagetitle', 'Setting')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
    .choices__inner { min-height: 38px; }
</style>
@endpush

@section('content')

<div class="col-12 col-lg-12">
    <!-- ✅ CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">


        <div class="card-body">
            <div class="row">
                <!-- LEFT SIDEBAR (Tab) -->
                <div class="col-md-2 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#employee" role="tab">
                                <i class="fas fa-user me-2"></i> Employee
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_position" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Job Position
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_level" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Job Level
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#director" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Director
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#division" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Division
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#department" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Department
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#section" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Section
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#organization" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Organization
                            </a>
                        </li>


                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="employee">
                            @include('pages.settings.employee')
                        </div>
                        <div class="tab-pane fade" id="job_position">
                            @include('pages.settings.JobPosition')
                        </div>
                        <div class="tab-pane fade" id="job_level">
                            @include('pages.settings.JobLevel')
                        </div>
                        <div class="tab-pane fade" id="director">
                            @include('pages.settings.director')
                        </div>
                        <div class="tab-pane fade" id="division">
                            @include('pages.settings.division')
                        </div>
                        <div class="tab-pane fade" id="department">
                            @include('pages.settings.department')
                        </div>
                        <div class="tab-pane fade" id="section">
                            @include('pages.settings.section')
                        </div>
                        <div class="tab-pane fade" id="organization">
                            <div id="orgTreeContainer">
                                {{-- Will be loaded via AJAX --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
    window.masterChoices = {};
    window.masterData = {};

    /**
     * Inisialisasi Choices.js pada elemen select
     */
    function initChoices(elementId, placeholder = "Select option") {
        const element = document.getElementById(elementId);
        if (!element) return;

        // Jika sudah ada instance, destroy dulu
        if (window.masterChoices[elementId]) {
            window.masterChoices[elementId].destroy();
        }

        window.masterChoices[elementId] = new Choices(element, {
            searchEnabled: true,
            itemSelectText: '',
            allowHTML: true,
            shouldSort: false,
            removeItemButton: false
        });
    }

    /**
     * Mengisi <select> dengan data array.
     * TIDAK auto-init Choices.js — caller bertanggung jawab init via initChoices()
     * atau Choices.js sudah di-manage via shown.bs.modal / hidden.bs.modal.
     *
     * Jika instance Choices.js sudah ada (window.masterChoices[id]), gunakan
     * setChoices() agar tidak merusak instance yang sedang aktif.
     */
    function populateSelect(elementId, data, selectedValue = null, labelField = 'name') {
        const element = document.getElementById(elementId);
        if (!element) return;

        const items = data || [];

        if (window.masterChoices[elementId]) {
            // Instance sudah ada → update via Choices API
            const instance = window.masterChoices[elementId];
            const choiceItems = [{ value: '', label: '-- Select Option --', selected: !selectedValue, disabled: true }];
            items.forEach(item => {
                choiceItems.push({
                    value: String(item.id),
                    label: item[labelField] || '',
                    selected: selectedValue && String(selectedValue) === String(item.id)
                });
            });
            instance.clearStore();
            instance.setChoices(choiceItems, 'value', 'label', true);
        } else {
            // Belum ada instance → set HTML options saja, jangan init Choices.js
            // (Choices.js akan di-init saat modal shown atau secara eksplisit)
            let html = '<option value="" disabled selected>-- Select Option --</option>';
            items.forEach(item => {
                const sel = selectedValue && String(selectedValue) === String(item.id) ? 'selected' : '';
                html += `<option value="${item.id}" ${sel}>${item[labelField] || ''}</option>`;
            });
            element.innerHTML = html;
        }
    }

    /**
     * Ambil data master terbaru dari server
     */
    function refreshMasterOptions(callback = null) {
        $.ajax({
            url: "{{ route('master.options') }}",
            method: "GET",
            success: function(res) {
                if (res.success) {
                    console.log("Master options refreshed:", res.data);
                    window.masterData = res.data;
                    
                    // Trigger event global agar partial lain bisa update
                    $(document).trigger('masterDataRefreshed', [res.data]);

                    if (typeof callback === 'function') callback(res.data);
                }
            },
            error: function(xhr) {
                console.error("Failed to refresh master options", xhr.responseText);
            }
        });

        // Juga refresh pohon organisasi
        refreshOrgTree();
    }

    /**
     * Ambil pohon organisasi terbaru dari server (JSON) dan render di JS
     */
    function refreshOrgTree() {
        $.ajax({
            url: "{{ route('master.organization') }}",
            method: "GET",
            success: function(res) {
                if (!res.success || !res.data) return;
                var html = buildOrgTree(res.data);
                $('#orgTreeContainer').html(html);
            }
        });
    }

    function buildOrgTree(directors) {
        if (!directors || !directors.length) return '<p class="text-muted">No organization data.</p>';
        var html = '<ul class="list-unstyled org-tree">';
        directors.forEach(function(dir) {
            html += '<li class="mb-2"><strong><i class="fas fa-building me-1 text-primary"></i>' + escHtml(dir.name) + '</strong>';
            if (dir.divisions && dir.divisions.length) {
                html += '<ul class="list-unstyled ms-4 mt-1">';
                dir.divisions.forEach(function(div) {
                    html += '<li class="mb-1"><i class="fas fa-sitemap me-1 text-success"></i>' + escHtml(div.name);
                    if (div.departments && div.departments.length) {
                        html += '<ul class="list-unstyled ms-4 mt-1">';
                        div.departments.forEach(function(dept) {
                            html += '<li class="mb-1"><i class="fas fa-users me-1 text-info"></i>' + escHtml(dept.name);
                            if (dept.sections && dept.sections.length) {
                                html += '<ul class="list-unstyled ms-4 mt-1">';
                                dept.sections.forEach(function(sec) {
                                    html += '<li><i class="fas fa-user me-1 text-secondary"></i>' + escHtml(sec.name) + '</li>';
                                });
                                html += '</ul>';
                            }
                            html += '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
            }
            html += '</li>';
        });
        html += '</ul>';
        return html;
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    $(document).ready(function() {
        refreshMasterOptions();
    });
    </script>
    @endpush