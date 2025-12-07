@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Company Policy')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/quill/quill.bubble.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/quill/quill.snow.css') }}">
    <!-- Simplebar Css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex">
                        <div class="col-md-4 col-xl-3 col-xxl-2 me-2">
                            {{-- <select id="status-choice">
                            <option value="all">All Years</option>
                            <option value="uk1">2025</option>
                            <option value="uk2">2024</option>
                            <option value="uk3">2023</option>
                        </select> --}}
                        </div>
                        <div class="col-md-4 col-xl-3 col-xxl-2">
                            {{-- <select id="priority-choice">
                                <option value="javascript">High</option>
                                <option value="python">Medium</option>
                                <option value="java">Low</option>
                            </select> --}}
                        </div>
                        <div class="col-md-4 col-xl-6 col-xxl-8 text-end">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#extraLargeModel">
                                <i class="bi bi-plus-circle-dotted me-2"></i>Add Company Policy
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-10">
                        <div class="table-box table-responsive">
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
                                rel="stylesheet">

                            <style>
                                /* Tambahan kecil agar panah animasi saat dibuka */
                                .accordion-toggle i.bi-chevron-down {
                                    transition: transform 0.2s ease-in-out;
                                }

                                .accordion-toggle[aria-expanded="true"] i.bi-chevron-down {
                                    transform: rotate(180deg);
                                }

                                .child-wrapper {
                                    background: #f8f9fa;
                                    padding: 10px 15px;
                                    border-radius: 4px;
                                }

                                .child-wrapper table {
                                    margin-bottom: 0;
                                }
                            </style>

                            <table id="companyPolicyTable" class="display table table-striped table-bordered"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tahun</th>
                                        <th>Jumlah Strategic Goals</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($policies as $index => $policy)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $policy->tahun }}</td>
                                            <td>{{ $policy->details_count }}</td>
                                            <td>
                                                <a href="{{ route('company-policy.pdf', $policy->id) }}" target="_blank"
                                                    class="btn btn-link btn-sm">
                                                    Document PDF
                                                </a>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm btn-detail"
                                                    data-id="{{ $policy->id }}">
                                                    Detail
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                    data-id="{{ $policy->id }}">
                                                    Delete
                                                </button>

                                                <form id="delete-form-{{ $policy->id }}"
                                                    action="{{ route('company-policy.destroy', $policy->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- HIDDEN TEMPLATE DETAIL (tabel) --}}
                            <div id="hidden-details" style="display:none;">
                                @foreach ($policies as $policy)
                                    <div id="detail-{{ $policy->id }}">
                                        @if ($policy->details->isEmpty())
                                            <div class="alert alert-secondary mb-0">
                                                Belum ada Strategic Goals untuk dokumen ini.
                                            </div>
                                        @else
                                            <div class="child-wrapper">
                                                <strong>Strategic Goals – {{ $policy->nama_dokumen }}
                                                    ({{ $policy->tahun }})</strong>
                                                <table class="table table-sm table-bordered mt-2">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 40px;">No</th>
                                                            <th>Strategic Goal (EN)</th>
                                                            <th>Description (EN)</th>
                                                            <th>Strategic Goal (ID)</th>
                                                            <th>Description (ID)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($policy->details as $i => $detail)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{!! $detail->strategic_goal !!}</td>
                                                                <td>{!! $detail->description !!}</td>
                                                                <td>{!! $detail->strategic_goal_id !!}</td>
                                                                <td>{!! $detail->description_id !!}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
    <form id="companyPolicyForm" action="{{ route('company-policy.store') }}" method="POST">
        @csrf

        <div class="modal fade" id="extraLargeModel" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="extraLargeModelLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraLargeModelLabel">Add Company Policy</h5>
                        <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ri-close-large-line fw-semibold"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-xl-12">
                                <label class="form-label" for="form-select-01"><b>Year</b></label>
                                <select class="form-select" id="form-select-01" name="tahun"
                                    aria-label="Default select example">
                                    <option value="">Select</option>
                                    @for ($year = 2023; $year <= date('Y') + 1; $year++)
                                        @php $selected = ''; @endphp
                                        @if ($year == date('Y') + 1)
                                            @php $selected = "selected='selected'"; @endphp
                                        @endif
                                        <option {{ $selected }} value="{{ $year }}">{{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- HEADER --}}
                        <div class="row mb-3">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Header</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor">
                                            <h3>THE COMPANY POLICY OF FY2026</h3>
                                            <h3>PT PEROKSIDA INDONESIA PRATAMA</h3>
                                            <p>=================================</p>
                                            <h3>[FOR THE PREPARATION OF THE COMPANY BUDGET FOR FISCAL YEAR 2026]</h3>
                                            <p>Cikampek, {{ date('d F Y') }}</p>
                                        </div>
                                        <input type="hidden" name="header" id="headerInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CONTENTS --}}
                        <div class="row mb-3">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">CONTENTS:</h5>
                                        <h5 class="card-title mb-0">English:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor2_1">
                                            <h3>REFER TO:</h3><br><br>
                                            <h3>CONSIDERING:</h3><br><br>
                                            <h3>DECISION:</h3><br><br>
                                            <h3>Background:</h3>
                                        </div>
                                        <input type="hidden" name="contents_en" id="contentsEnInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">&nbsp;</h5>
                                        <h5 class="card-title mb-0">Indonesia:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor2_2">
                                            <h3>MENGACU PADA:</h3><br><br>
                                            <h3>MEMPERTIMBANGKAN:</h3><br><br>
                                            <h3>MEMUTUSKAN:</h3><br><br>
                                            <h3>Latar belakang:</h3>
                                        </div>
                                        <input type="hidden" name="contents_id" id="contentsIdInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PROLOGUE --}}
                        <div class="row mb-3">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">COMPANY POLICY:</h5>
                                        <h5 class="card-title mb-0">English:</h5>
                                        <h5 class="card-title mb-0">Prologue:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor3_1"></div>
                                        <input type="hidden" name="prologue_en" id="prologueEnInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">&nbsp;</h5>
                                        <h5 class="card-title mb-0">Indonesia:</h5>
                                        <h5 class="card-title mb-0">Pendahuluan:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor3_2"></div>
                                        <input type="hidden" name="prologue_id" id="prologueIdInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- COMPANY POLICY (dynamic rows) --}}
                        <div class="row">
                            <div class="col-12 d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h5 class="card-title mb-0">COMPANY POLICY:</h5>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="addCompanyPolicyRow">
                                    + Add Row
                                </button>
                            </div>
                        </div>

                        <div id="companyPolicyContainer">
                            <div class="row company-policy-row mb-3" data-index="0">
                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-0">English</h5>
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-policy-row">
                                                Delete
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <label class="form-label fw-semibold">Core Company Policy (English)</label>
                                            <div class="company-policy-core-en"></div>
                                            <input type="hidden" name="company_policy_core_en[]"
                                                class="company-policy-core-en-input">

                                            <label class="form-label fw-semibold mt-3">Description Company Policy
                                                (English)</label>
                                            <div class="company-policy-desc-en"></div>
                                            <input type="hidden" name="company_policy_desc_en[]"
                                                class="company-policy-desc-en-input">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Indonesia</h5>
                                        </div>
                                        <div class="card-body">
                                            <label class="form-label fw-semibold">Core Company Policy (Indonesia)</label>
                                            <div class="company-policy-core-id"></div>
                                            <input type="hidden" name="company_policy_core_id[]"
                                                class="company-policy-core-id-input">

                                            <label class="form-label fw-semibold mt-3">Deskripsi Company Policy
                                                (Indonesia)</label>
                                            <div class="company-policy-desc-id"></div>
                                            <input type="hidden" name="company_policy_desc_id[]"
                                                class="company-policy-desc-id-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TEMPLATE ROW (untuk clone) --}}
                        <template id="companyPolicyRowTemplate">
                            <div class="row company-policy-row mb-3" data-index="{INDEX}">
                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-0">English</h5>
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-policy-row">
                                                Delete
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <label class="form-label fw-semibold">Core Company Policy (English)</label>
                                            <div class="company-policy-core-en"></div>
                                            <input type="hidden" name="company_policy_core_en[]"
                                                class="company-policy-core-en-input">

                                            <label class="form-label fw-semibold mt-3">Description Company Policy
                                                (English)</label>
                                            <div class="company-policy-desc-en"></div>
                                            <input type="hidden" name="company_policy_desc_en[]"
                                                class="company-policy-desc-en-input">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Indonesia</h5>
                                        </div>
                                        <div class="card-body">
                                            <label class="form-label fw-semibold">Core Company Policy (Indonesia)</label>
                                            <div class="company-policy-core-id"></div>
                                            <input type="hidden" name="company_policy_core_id[]"
                                                class="company-policy-core-id-input">

                                            <label class="form-label fw-semibold mt-3">Deskripsi Company Policy
                                                (Indonesia)</label>
                                            <div class="company-policy-desc-id"></div>
                                            <input type="hidden" name="company_policy_desc_id[]"
                                                class="company-policy-desc-id-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- CLOSING --}}
                        <div class="row mb-3">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Closing Statements:</h5>
                                        <h5 class="card-title mb-0">English:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor5_1"></div>
                                        <input type="hidden" name="closing_en" id="closingEnInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">&nbsp;</h5>
                                        <h5 class="card-title mb-0">Indonesia:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor5_2"></div>
                                        <input type="hidden" name="closing_id" id="closingIdInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SIGNATURE --}}
                        <div class="row mb-3">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Signature:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="snowEditor6_1">
                                            <h3>THE BOARD OF DIRECTOR/DEWAN DIREKSI</h3>
                                            <table>
                                                <tr>
                                                    <td>President Director</td>
                                                    <td>Operations and Production Director</td>
                                                    <td>Finance and General Affair Director</td>
                                                </tr>
                                                <tr>
                                                    <td><u><b>Yasuhiko Takaizumi</b></u></td>
                                                    <td><u><b>Daichi Ogawa</b></u></td>
                                                    <td><u><b>Yara Budhi Widowati</b></u></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <input type="hidden" name="signature" id="signatureInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> {{-- modal-body --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `
        <ul style='text-align:left;'>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    `
            });
        </script>
    @endif
@endsection

@section('js')
    <script src="{{ asset('assets/libs/quill/quill.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#companyPolicyTable').DataTable({
                language: {
                    paginate: {
                        first: "&laquo;&laquo;",
                        previous: "&laquo;",
                        next: "&raquo;",
                        last: "&raquo;&raquo;"
                    }
                }
            });

            // Klik tombol Detail → tampilkan / sembunyikan child row berisi tabel detail
            $('#companyPolicyTable tbody').on('click', '.btn-detail', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var id = $(this).data('id');
                var html = $('#detail-' + id).html();

                if (row.child.isShown()) {
                    // Kalau sudah terbuka → tutup
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // (opsional) tutup child lain biar rapi
                    table.rows().every(function() {
                        if (this.child.isShown()) {
                            this.child.hide();
                            $(this.node()).removeClass('shown');
                        }
                    });

                    row.child(html).show();
                    tr.addClass('shown');
                }
            });

            // Klik tombol Delete → SweetAlert konfirmasi → submit form delete
            $('#companyPolicyTable tbody').on('click', '.btn-delete', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus dokumen ini?',
                    text: "Dokumen dan semua strategic goals akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#delete-form-' + id).submit();
                    }
                });
            });

        });
    </script>

    <script>
        let companyPolicyIndex = 0;
        const companyPolicyContainer = document.getElementById('companyPolicyContainer');
        const companyPolicyTemplate = document.getElementById('companyPolicyRowTemplate');
        const addCompanyPolicyRowBtn = document.getElementById('addCompanyPolicyRow');

        let headerEditor,
            contentsEnEditor,
            contentsIdEditor,
            prologueEnEditor,
            prologueIdEditor,
            closingEnEditor,
            closingIdEditor,
            signatureEditor;

        const policyEditors = {
            coreEn: [],
            descEn: [],
            coreId: [],
            descId: []
        };

        function initMainEditors() {
            headerEditor = new Quill('#snowEditor', {
                theme: 'snow'
            });
            contentsEnEditor = new Quill('#snowEditor2_1', {
                theme: 'snow'
            });
            contentsIdEditor = new Quill('#snowEditor2_2', {
                theme: 'snow'
            });
            prologueEnEditor = new Quill('#snowEditor3_1', {
                theme: 'snow'
            });
            prologueIdEditor = new Quill('#snowEditor3_2', {
                theme: 'snow'
            });
            closingEnEditor = new Quill('#snowEditor5_1', {
                theme: 'snow'
            });
            closingIdEditor = new Quill('#snowEditor5_2', {
                theme: 'snow'
            });
            signatureEditor = new Quill('#snowEditor6_1', {
                theme: 'snow'
            });
        }

        function initPolicyRowEditors(rowElement) {
            const coreEnDiv = rowElement.querySelector('.company-policy-core-en');
            const descEnDiv = rowElement.querySelector('.company-policy-desc-en');
            const coreIdDiv = rowElement.querySelector('.company-policy-core-id');
            const descIdDiv = rowElement.querySelector('.company-policy-desc-id');

            const coreEnQuill = new Quill(coreEnDiv, {
                theme: 'snow'
            });
            const descEnQuill = new Quill(descEnDiv, {
                theme: 'snow'
            });
            const coreIdQuill = new Quill(coreIdDiv, {
                theme: 'snow'
            });
            const descIdQuill = new Quill(descIdDiv, {
                theme: 'snow'
            });

            policyEditors.coreEn.push(coreEnQuill);
            policyEditors.descEn.push(descEnQuill);
            policyEditors.coreId.push(coreIdQuill);
            policyEditors.descId.push(descIdQuill);
        }

        function addCompanyPolicyRow() {
            companyPolicyIndex++;

            const tpl = companyPolicyTemplate.innerHTML.replace(/{INDEX}/g, companyPolicyIndex);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = tpl.trim();
            const newRow = wrapper.firstElementChild;

            companyPolicyContainer.appendChild(newRow);
            initPolicyRowEditors(newRow);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initMainEditors();

            const firstRow = companyPolicyContainer.querySelector('.company-policy-row');
            if (firstRow) {
                initPolicyRowEditors(firstRow);
            }

            addCompanyPolicyRowBtn.addEventListener('click', function() {
                addCompanyPolicyRow();
            });

            companyPolicyContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-delete-policy-row')) {
                    const row = e.target.closest('.company-policy-row');

                    const rows = Array.from(companyPolicyContainer.querySelectorAll('.company-policy-row'));
                    if (rows.length === 1) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Minimal harus ada 1 row COMPANY POLICY.'
                        });
                        return;
                    }

                    const index = rows.indexOf(row);
                    if (index > -1) {
                        policyEditors.coreEn.splice(index, 1);
                        policyEditors.descEn.splice(index, 1);
                        policyEditors.coreId.splice(index, 1);
                        policyEditors.descId.splice(index, 1);
                    }

                    row.remove();
                }
            });

            const form = document.getElementById('companyPolicyForm');
            form.addEventListener('submit', function(event) {
                // ====== VALIDASI CLIENT-SIDE ======
                const tahun = document.getElementById('form-select-01').value;

                // 1. Cek Year wajib diisi
                if (!tahun) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        text: 'Silakan pilih Tahun terlebih dahulu.'
                    });
                    return;
                }

                // 2. Opsional: cek minimal ada satu isi Company Policy (core_en atau core_id)
                let hasPolicyContent = false;
                policyEditors.coreEn.forEach(ed => {
                    if (ed.getText().trim().length > 0) {
                        hasPolicyContent = true;
                    }
                });
                policyEditors.coreId.forEach(ed => {
                    if (ed.getText().trim().length > 0) {
                        hasPolicyContent = true;
                    }
                });

                if (!hasPolicyContent) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        text: 'Minimal isi salah satu Core Company Policy.'
                    });
                    return;
                }

                // ====== JIKA LOLOS VALIDASI → sinkronisasi ke hidden input ======
                document.getElementById('headerInput').value = headerEditor.root.innerHTML;
                document.getElementById('contentsEnInput').value = contentsEnEditor.root.innerHTML;
                document.getElementById('contentsIdInput').value = contentsIdEditor.root.innerHTML;
                document.getElementById('prologueEnInput').value = prologueEnEditor.root.innerHTML;
                document.getElementById('prologueIdInput').value = prologueIdEditor.root.innerHTML;
                document.getElementById('closingEnInput').value = closingEnEditor.root.innerHTML;
                document.getElementById('closingIdInput').value = closingIdEditor.root.innerHTML;
                document.getElementById('signatureInput').value = signatureEditor.root.innerHTML;

                const rows = companyPolicyContainer.querySelectorAll('.company-policy-row');
                rows.forEach((row, i) => {
                    const coreEnInput = row.querySelector('.company-policy-core-en-input');
                    const descEnInput = row.querySelector('.company-policy-desc-en-input');
                    const coreIdInput = row.querySelector('.company-policy-core-id-input');
                    const descIdInput = row.querySelector('.company-policy-desc-id-input');

                    coreEnInput.value = policyEditors.coreEn[i].root.innerHTML;
                    descEnInput.value = policyEditors.descEn[i].root.innerHTML;
                    coreIdInput.value = policyEditors.coreId[i].root.innerHTML;
                    descIdInput.value = policyEditors.descId[i].root.innerHTML;
                });

                // biarkan form lanjut submit ke server
            });
        });
    </script>

@endsection
