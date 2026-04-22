@extends('layouts.master')

@section('title', 'Setting | Master')
@section('title-sub', 'Master')
@section('pagetitle', 'Setting')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
    .choices__inner { min-height: 38px; }
</style>
@endsection

@section('content')

<div class="col-12 col-lg-12">
    <!-- ✅ CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">


        <div class="card-body">
            <div>
                <!-- TOP TABS -->
                <ul class="nav nav-pills nav-custom nav-success mb-3" role="tablist" style="overflow-x: auto; flex-wrap: nowrap; padding-bottom: 5px;">
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
                            <i class="fas fa-sitemap me-2"></i> Organization
                        </a>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content">
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
                initOrgTreeToggle();
            }
        });
    }

    function buildOrgTree(directors) {
        if (!directors || !directors.length) return '<p class="text-muted">No organization data.</p>';
        
        var css = `
            <style>
                .org-container { padding: 20px; overflow-x: auto; background: #f8fafc; border-radius: 12px; }
                .org-tree { display: flex; justify-content: center; padding-top: 20px; position: relative; margin: 0; padding-left: 0; }
                .org-tree ul { padding-top: 20px; position: relative; transition: all 0.5s; display: flex; justify-content: center; gap: 16px; margin: 0; padding-left: 0; }
                .org-tree li { list-style-type: none; text-align: center; position: relative; padding: 0 5px; }
                .org-tree ul::before { content: ''; position: absolute; top: 0; left: 10%; right: 10%; height: 1px; background: rgba(148, 163, 184, 0.4); }
                .org-tree li::before { content: ''; position: absolute; top: -20px; left: 50%; transform: translateX(-50%); width: 2px; height: 20px; background: rgba(148, 163, 184, 0.4); }
                .org-node { display: inline-block; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(148, 163, 184, 0.2); background: #fff; min-width: 160px; cursor: default; box-shadow: 0 2px 6px rgba(2, 6, 23, 0.06); position: relative; z-index: 1; }
                .org-node .title { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
                .org-node .meta { font-size: 0.75rem; color: #64748b; margin-top: 4px; }
                .org-node .badge { display: inline-block; margin-top: 6px; padding: 2px 8px; font-size: 11px; border-radius: 999px; background: rgba(34, 197, 94, 0.1); color: #059669; border: 1px solid rgba(34, 197, 94, 0.2); }
                .org-node .badge.head { background: rgba(59, 130, 246, 0.1); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.2); display: block; margin-top: 4px; }
                .org-tree .children { margin-top: 8px; display: flex; gap: 24px; justify-content: center; }
                .org-node[data-toggle] { cursor: pointer; }
                .org-node:hover { transform: translateY(-3px); transition: transform 0.15s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            </style>
        `;

        var html = css + '<div class="org-container"><ul class="org-tree">';
        
        directors.forEach(function(dir) {
            html += '<li>';
            html += '<div class="org-node" data-toggle="true">';
            html += '<div class="title">' + escHtml(dir.name) + '</div>';
            html += '<div class="meta">' + (dir.code ? escHtml(dir.code) + ' &bull; ' : '') + escHtml(dir.status) + '</div>';
            if (dir.head_employee_name) {
                html += '<div class="badge head"><i class="bi bi-person-fill"></i> ' + escHtml(dir.head_employee_name) + '</div>';
            }
            html += '</div>';

            if (dir.divisions && dir.divisions.length) {
                html += '<ul class="children">';
                dir.divisions.forEach(function(div) {
                    html += '<li>';
                    html += '<div class="org-node" data-toggle="true">';
                    html += '<div class="title">' + escHtml(div.name) + '</div>';
                    html += '<div class="meta">' + escHtml(div.status) + '</div>';
                    if (div.head_employee_name) {
                        html += '<div class="badge head"><i class="bi bi-person-fill"></i> ' + escHtml(div.head_employee_name) + '</div>';
                    }
                    html += '</div>';

                    if (div.departments && div.departments.length) {
                        html += '<ul class="children">';
                        div.departments.forEach(function(dept) {
                            html += '<li>';
                            html += '<div class="org-node" data-toggle="true">';
                            html += '<div class="title">' + escHtml(dept.name) + '</div>';
                            html += '<div class="meta">' + escHtml(dept.status) + '</div>';
                            if (dept.head_employee_name) {
                                html += '<div class="badge head"><i class="bi bi-person-fill"></i> ' + escHtml(dept.head_employee_name) + '</div>';
                            }
                            html += '</div>';

                            if (dept.sections && dept.sections.length) {
                                html += '<ul class="children">';
                                dept.sections.forEach(function(sec) {
                                    html += '<li>';
                                    html += '<div class="org-node">';
                                    html += '<div class="title">' + escHtml(sec.name) + '</div>';
                                    html += '<div class="meta">' + escHtml(sec.status) + '</div>';
                                    if (sec.head_employee_name) {
                                        html += '<div class="badge head"><i class="bi bi-person-fill"></i> ' + escHtml(sec.head_employee_name) + '</div>';
                                    }
                                    html += '</div>';
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
            }
            html += '</li>';
        });

        html += '</ul></div>';
        return html;
    }

    function initOrgTreeToggle() {
        document.querySelectorAll('.org-node[data-toggle="true"]').forEach(function(node) {
            node.addEventListener('click', function(e) {
                e.stopPropagation();
                var parent = node.parentElement;
                var childUl = parent.querySelector(':scope > ul.children');
                if (!childUl) return;
                childUl.style.display = (childUl.style.display === 'none') ? 'flex' : 'none';
            });
        });
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