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
     * Mengisi select dengan data baru dan refresh Choices.js
     */
    function populateSelect(elementId, data, selectedValue = null, labelField = 'name') {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`Element #${elementId} not found for populateSelect`);
            return;
        }

        console.log(`Populating #${elementId} with ${data.length} items`);

        // Siapkan choices
        const choices = [{
            value: '',
            label: '-- Select Option --',
            selected: !selectedValue,
            disabled: true
        }];

        data.forEach(item => {
            choices.push({
                value: item.id.toString(),
                label: item[labelField],
                selected: selectedValue && selectedValue.toString() === item.id.toString()
            });
        });

        // Jika sudah ada instance Choices.js
        if (window.masterChoices[elementId]) {
            const instance = window.masterChoices[elementId];
            instance.clearStore();
            instance.setChoices(choices, 'value', 'label', true);
        } else {
            // Jika belum ada instance, isi HTML manual dulu lalu init
            let html = '<option value="" disabled selected>-- Select Option --</option>';
            data.forEach(item => {
                const isSelected = selectedValue && selectedValue.toString() === item.id.toString();
                html += `<option value="${item.id}" ${isSelected ? 'selected' : ''}>${item[labelField]}</option>`;
            });
            element.innerHTML = html;
            
            // Beri sedikit delay untuk inisialisasi Choices.js jika elemen baru muncul
            setTimeout(() => {
                initChoices(elementId);
            }, 50);
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
     * Ambil pohon organisasi terbaru dari server (HTML partial)
     */
    function refreshOrgTree() {
        $.ajax({
            url: "{{ route('master.organization') }}",
            method: "GET",
            success: function(html) {
                $('#orgTreeContainer').html(html);

                // Re-init toggle listener jika ada di partial
                if (typeof initOrgTreeToggle === 'function') {
                    initOrgTreeToggle();
                }
            }
        });
    }

    $(document).ready(function() {
        refreshMasterOptions();
    });
    </script>
    @endpush