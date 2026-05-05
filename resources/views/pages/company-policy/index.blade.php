@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')
@section('title-sub', 'Budget Control')
@section('pagetitle', 'Company Policy')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/quill/quill.snow.css') }}">
<style>
    .cp-card { border: 1px solid #e9ecef; border-radius: 10px; }
    .cp-section-title { font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
    .cp-help { font-size: 12px; color: #6c757d; }
    .cp-two-col-label { font-size: 13px; font-weight: 700; color: #495057; }
    .cp-row-actions { white-space: nowrap; }
    .ql-editor { min-height: 110px; }
    .ql-editor.ql-blank::before { font-style: normal; color: #adb5bd; }
    .policy-editor .ql-editor { min-height: 150px; }
    .signature-preview-box { min-height: 120px; border: 1px dashed #ced4da; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #adb5bd; background: #f8f9fa; }
    #companyPolicyTable tbody td { padding: 6px 8px; vertical-align: middle; line-height: 1.2; }
    #companyPolicyTable thead th { padding: 8px; vertical-align: middle; }
    #companyPolicyTable .btn { padding: 4px 6px; font-size: 12px; line-height: 1; }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Company Policy</h5>
                    <button type="button" class="btn btn-workplan btn-sm" data-bs-toggle="modal" data-bs-target="#companyPolicyModal" id="btnAddPolicy">
                        <i class="bi bi-plus-circle-dotted me-2"></i>Add Company Policy
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="companyPolicyTable" class="display table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun</th>
                                    <th>Jumlah Policy</th>
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
                                        <a href="{{ route('company-policy.pdf', $policy->id) }}" target="_blank" class="btn btn-workplan btn-sm">Document PDF</a>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm btn-detail" data-id="{{ $policy->id }}"><i class="bi bi-eye"></i></button>
                                        <button type="button" class="btn btn-warning btn-sm btn-edit" data-id="{{ $policy->id }}"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $policy->id }}"><i class="bi bi-trash"></i></button>
                                        <form id="delete-form-{{ $policy->id }}" action="{{ route('company-policy.destroy', $policy->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="hidden-details" style="display:none;">
                        @foreach ($policies as $policy)
                        <div id="detail-{{ $policy->id }}">
                            @if ($policy->details->isEmpty())
                                <div class="alert alert-secondary mb-0">Belum ada Company Policy untuk dokumen ini.</div>
                            @else
                                <div class="p-3 bg-light rounded">
                                    <strong>Company Policy - {{ $policy->tahun }}</strong>
                                    <table class="table table-sm table-bordered mt-2 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40px;">No</th>
                                                <th>Policy EN</th>
                                                <th>Description EN</th>
                                                <th>Policy ID</th>
                                                <th>Description ID</th>
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

<form id="companyPolicyForm" action="{{ route('company-policy.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" id="formMethod" value="POST">

    <div class="modal fade" id="companyPolicyModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="companyPolicyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="companyPolicyModalLabel">Add Company Policy</h5>
                        <div class="cp-help">Form disusun mengikuti format final PDF: dua kolom English dan Indonesia sampai bagian tanda tangan.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- BASIC INFORMATION --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header"><span class="cp-section-title">1. Header Dokumen</span></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Fiscal Year / Tahun</label>
                                    <select class="form-select" id="tahun" name="tahun" required>
                                        <option value="">Select Year</option>
                                        @for ($year = 2023; $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') + 1 ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" id="company_name" value="PT PEROKSIDA INDONESIA PRATAMA" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Place & Date</label>
                                    <input type="text" class="form-control" name="place_date" id="place_date" value="Cikampek, {{ date('j F Y') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Document Title</label>
                                    <input type="text" class="form-control" name="document_title" id="document_title" value="THE COMPANY POLICY OF FY2026" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Subtitle / Purpose</label>
                                    <input type="text" class="form-control" name="subtitle" id="subtitle" value="FOR THE PREPARATION OF THE COMPANY BUDGET FOR FISCAL YEAR 2026" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- REFER TO --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="cp-section-title">2. Refer To / Mengacu Pada</span>
                            <button type="button" class="btn btn-sm btn-primary" data-add-row="referToRows">+ Add Row</button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6 cp-two-col-label">REFER TO:</div>
                                <div class="col-md-6 cp-two-col-label">MENGACU PADA:</div>
                            </div>
                            <div id="referToRows"></div>
                        </div>
                    </div>

                    {{-- CONSIDERING --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="cp-section-title">3. Considering / Mempertimbangkan</span>
                            <button type="button" class="btn btn-sm btn-primary" data-add-row="consideringRows">+ Add Row</button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6 cp-two-col-label">CONSIDERING:</div>
                                <div class="col-md-6 cp-two-col-label">MEMPERTIMBANGKAN:</div>
                            </div>
                            <div id="consideringRows"></div>
                        </div>
                    </div>

                    {{-- DECISION AND BACKGROUND --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header"><span class="cp-section-title">4. Decision & Background / Memutuskan & Latar Belakang</span></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">DECISION:</label>
                                    <textarea class="form-control" name="decision_en" id="decision_en" rows="3">The Board of Directors stated The Company's Policy for the 2026 financial year, as follows:</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">MEMUTUSKAN:</label>
                                    <textarea class="form-control" name="decision_id" id="decision_id" rows="3">Dewan Direksi menyatakan Kebijakan Perusahaan untuk tahun buku 2026, sebagai berikut:</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Background</label>
                                    <div id="background_en_editor" class="policy-editor"></div>
                                    <input type="hidden" name="background_en" id="background_en">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Latar Belakang</label>
                                    <div id="background_id_editor" class="policy-editor"></div>
                                    <input type="hidden" name="background_id" id="background_id">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PROLOGUE --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header"><span class="cp-section-title">5. Company Policy Prologue / Pendahuluan</span></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Policy FY2026</label>
                                    <div id="prologue_en_editor"></div>
                                    <input type="hidden" name="prologue_en" id="prologue_en">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Policy Tahun 2026</label>
                                    <div id="prologue_id_editor"></div>
                                    <input type="hidden" name="prologue_id" id="prologue_id">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- POLICY POINTS --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="cp-section-title">6. Policy Points / Butir Kebijakan</span>
                            <button type="button" class="btn btn-sm btn-primary" id="addPolicyPoint">+ Add Policy Point</button>
                        </div>
                        <div class="card-body">
                            <div id="policyPointRows"></div>
                        </div>
                    </div>

                    {{-- CLOSING --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header"><span class="cp-section-title">7. Closing Statement / Pernyataan Penutup</span></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Closing Statement</label>
                                    <div id="closing_en_editor"></div>
                                    <input type="hidden" name="closing_en" id="closing_en">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pernyataan Penutup</label>
                                    <div id="closing_id_editor"></div>
                                    <input type="hidden" name="closing_id" id="closing_id">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SIGNATURE --}}
                    <div class="card cp-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="cp-section-title">8. Signature / Tanda Tangan Direksi</span>
                            <button type="button" class="btn btn-sm btn-primary" id="addSignatureRow">+ Add Signature</button>
                        </div>
                        <div class="card-body">
                            <div class="cp-help mb-3">Minimal 1 tanda tangan. Tambahkan atau hapus penandatangan sesuai kebutuhan dokumen.</div>
                            <div class="row g-3" id="signatureRows"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="bilingualRowTemplate">
    <div class="row g-2 align-items-start mb-2 bilingual-row">
        <div class="col-md-6"><textarea class="form-control" name="__NAME_EN__[]" rows="2" placeholder="English"></textarea></div>
        <div class="col-md-5"><textarea class="form-control" name="__NAME_ID__[]" rows="2" placeholder="Indonesia"></textarea></div>
        <div class="col-md-1 cp-row-actions"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Delete</button></div>
    </div>
</template>

<template id="policyPointTemplate">
    <div class="policy-point border rounded p-3 mb-3" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Policy Point <span class="policy-point-number"></span></h6>
            <button type="button" class="btn btn-outline-danger btn-sm remove-policy-point">Delete</button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Policy Statement (English)</label>
                <div class="policy-title-en policy-editor"></div>
                <input type="hidden" name="company_policy_core_en[]" class="policy-title-en-input">
                <label class="form-label fw-semibold mt-3">Policy Description (English)</label>
                <div class="policy-desc-en policy-editor"></div>
                <input type="hidden" name="company_policy_desc_en[]" class="policy-desc-en-input">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Pernyataan Kebijakan (Indonesia)</label>
                <div class="policy-title-id policy-editor"></div>
                <input type="hidden" name="company_policy_core_id[]" class="policy-title-id-input">
                <label class="form-label fw-semibold mt-3">Deskripsi Kebijakan (Indonesia)</label>
                <div class="policy-desc-id policy-editor"></div>
                <input type="hidden" name="company_policy_desc_id[]" class="policy-desc-id-input">
            </div>
        </div>
    </div>
</template>

<template id="signatureRowTemplate">
    <div class="col-md-4 signature-col">
        <div class="border rounded p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Signature <span class="signature-number"></span></h6>
                <button type="button" class="btn btn-outline-danger btn-sm remove-signature-row">Delete</button>
            </div>
            <label class="form-label fw-semibold">Position</label>
            <input type="text" class="form-control mb-2" name="signature_position[]" placeholder="e.g. President Director" required>
            <label class="form-label fw-semibold">Name</label>
            <input type="text" class="form-control mb-2" name="signature_name[]" placeholder="e.g. Nama Penandatangan" required>
            <label class="form-label fw-semibold">Signature Image</label>
            <input type="file" class="form-control" name="signature_image[]" accept="image/*">
            <div class="cp-help mt-1">Opsional. Jika dikosongkan, PDF menampilkan area tanda tangan kosong.</div>
        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session('success'))
<script>Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });</script>
@endif
@if (session('error'))
<script>Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });</script>
@endif
@if ($errors->any())
<script>
Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: `<ul style='text-align:left;'>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>` });
</script>
@endif
@endsection

@section('js')
<script src="{{ asset('assets/libs/quill/quill.js') }}"></script>
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>

<script>
    let backgroundEnEditor, backgroundIdEditor, prologueEnEditor, prologueIdEditor, closingEnEditor, closingIdEditor;
    const policyEditors = [];
    let policyPointIndex = 0;

    const defaultBackgroundEn = `<ol type="a"><li>The political, economic, social, cultural, and environmental dynamics at both national and global levels have a significant impact on the development of the Company.</li><li>The Company's internal conditions, particularly those related to the supply chain, quality, pricing, and availability of raw materials and utilities, continue to face significant challenges.</li></ol>`;
    const defaultBackgroundId = `<ol type="a"><li>Dinamika politik, ekonomi, sosial, budaya, serta lingkungan baik di tingkat nasional maupun global memberikan pengaruh signifikan terhadap perkembangan Perusahaan.</li><li>Kondisi internal Perusahaan, khususnya yang berkaitan dengan rantai pasokan, kualitas, harga, dan ketersediaan bahan baku serta utilitas masih menghadapi tantangan signifikan.</li></ol>`;
    const defaultPrologueEn = `<p>Taking into account the Company's external and internal conditions, and referring to government regulations as well as policies of the parent company, the Company has established the Company Policy for Fiscal Year 2026 as a guideline for the preparation of the business plan and budget.</p>`;
    const defaultPrologueId = `<p>Dengan mempertimbangkan kondisi eksternal dan internal Perusahaan, serta mengacu pada regulasi pemerintah dan kebijakan perusahaan induk, Perusahaan menetapkan Company Policy Tahun Buku 2026 sebagai pedoman dalam penyusunan rencana bisnis dan anggaran.</p>`;
    const defaultClosingEn = `<p>This Company Policy Statement for FY2026 is hereby conveyed as a reference for preparing more detailed business plans and budgets to support the implementation of the Company's business and operational activities.</p><p>This policy may be adjusted as needed, following the availability of final data from the budget calculation.</p>`;
    const defaultClosingId = `<p>Demikian pernyataan kebijakan manajemen FY2026 ini disampaikan sebagai acuan dalam menyusun rencana bisnis dan anggaran yang lebih detail untuk melaksanakan kegiatan bisnis dan operasional Perusahaan.</p><p>Penyesuaian terhadap kebijakan ini akan dilakukan jika diperlukan, seiring dengan tersedianya data final dari kalkulasi anggaran tahun berjalan.</p>`;

    function createEditor(selector, html = '') {
        const editor = new Quill(selector, { theme: 'snow' });
        editor.clipboard.dangerouslyPasteHTML(html || '');
        return editor;
    }

    function addBilingualRow(containerId, enName, idName, enValue = '', idValue = '') {
        const container = document.getElementById(containerId);
        const template = document.getElementById('bilingualRowTemplate').innerHTML
            .replace(/__NAME_EN__/g, enName)
            .replace(/__NAME_ID__/g, idName);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.trim();
        const row = wrapper.firstElementChild;
        row.querySelector(`[name="${enName}[]"]`).value = enValue;
        row.querySelector(`[name="${idName}[]"]`).value = idValue;
        container.appendChild(row);
    }

    function addDefaultBilingualRows() {
        document.getElementById('referToRows').innerHTML = '';
        document.getElementById('consideringRows').innerHTML = '';
        addBilingualRow('referToRows', 'refer_to_en', 'refer_to_id', 'Joint Venture Agreement (JVA) between PT Pupuk Kujang (PK) and Mitsubishi Gas Chemical Company, Inc. (MGC) dated October 27th, 1987.', 'Joint Venture Agreement (JVA) antara PT Pupuk Kujang dengan Mitsubishi Gas Chemical Company, Inc. (MGC) dated October 27th, 1987.');
        addBilingualRow('referToRows', 'refer_to_en', 'refer_to_id', 'The Articles of Association (AoA) of the Company and its amendments.', 'Anggaran Dasar Perusahaan beserta perubahannya.');
        addBilingualRow('referToRows', 'refer_to_en', 'refer_to_id', 'Mission and Vision of the Company.', 'Misi dan Visi Perusahaan.');
        addBilingualRow('consideringRows', 'considering_en', 'considering_id', 'Global and national economic, geo-politic, market and other situations.', 'Situasi ekonomi, politik, pasar dan lainnya di lingkungan global maupun nasional.');
        addBilingualRow('consideringRows', 'considering_en', 'considering_id', 'Internal company situation and condition.', 'Kondisi dan situasi internal Perusahaan.');
        addBilingualRow('consideringRows', 'considering_en', 'considering_id', 'Parent Company Policy and Management Meeting.', 'Kebijakan Perusahaan Induk dan Rapat Manajemen.');
    }

    function addPolicyPoint(data = {}) {
        policyPointIndex++;
        const template = document.getElementById('policyPointTemplate').innerHTML.replace(/__INDEX__/g, policyPointIndex);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.trim();
        const row = wrapper.firstElementChild;
        document.getElementById('policyPointRows').appendChild(row);

        const editorSet = {
            row,
            titleEn: new Quill(row.querySelector('.policy-title-en'), { theme: 'snow' }),
            descEn: new Quill(row.querySelector('.policy-desc-en'), { theme: 'snow' }),
            titleId: new Quill(row.querySelector('.policy-title-id'), { theme: 'snow' }),
            descId: new Quill(row.querySelector('.policy-desc-id'), { theme: 'snow' })
        };
        editorSet.titleEn.clipboard.dangerouslyPasteHTML(data.titleEn || '');
        editorSet.descEn.clipboard.dangerouslyPasteHTML(data.descEn || '');
        editorSet.titleId.clipboard.dangerouslyPasteHTML(data.titleId || '');
        editorSet.descId.clipboard.dangerouslyPasteHTML(data.descId || '');
        policyEditors.push(editorSet);
        refreshPolicyNumbers();
    }

    function refreshPolicyNumbers() {
        document.querySelectorAll('.policy-point').forEach((el, i) => {
            el.querySelector('.policy-point-number').textContent = i + 1;
        });
    }

    function addSignatureRow(position = '', name = '') {
        const container = document.getElementById('signatureRows');
        const template = document.getElementById('signatureRowTemplate').innerHTML;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.trim();
        const row = wrapper.firstElementChild;
        row.querySelector('[name="signature_position[]"]').value = position;
        row.querySelector('[name="signature_name[]"]').value = name;
        container.appendChild(row);
        refreshSignatureNumbers();
    }

    function refreshSignatureNumbers() {
        document.querySelectorAll('.signature-col').forEach((el, i) => {
            const numberEl = el.querySelector('.signature-number');
            if (numberEl) numberEl.textContent = i + 1;
        });
    }

    function resetSignatureRows() {
        const container = document.getElementById('signatureRows');
        container.innerHTML = '';
        addSignatureRow('President Director', 'Yasuhiko Takaizumi');
    }

    function resetForm() {
        $('#companyPolicyModalLabel').text('Add Company Policy');
        $('#companyPolicyForm').attr('action', "{{ route('company-policy.store') }}");
        $('#formMethod').val('POST');
        document.getElementById('companyPolicyForm').reset();
        document.getElementById('tahun').value = '{{ date('Y') + 1 }}';
        document.getElementById('company_name').value = 'PT PEROKSIDA INDONESIA PRATAMA';
        document.getElementById('document_title').value = 'THE COMPANY POLICY OF FY{{ date('Y') + 1 }}';
        document.getElementById('subtitle').value = 'FOR THE PREPARATION OF THE COMPANY BUDGET FOR FISCAL YEAR {{ date('Y') + 1 }}';
        document.getElementById('place_date').value = 'Cikampek, {{ date('j F Y') }}';

        addDefaultBilingualRows();
        backgroundEnEditor.clipboard.dangerouslyPasteHTML(defaultBackgroundEn);
        backgroundIdEditor.clipboard.dangerouslyPasteHTML(defaultBackgroundId);
        prologueEnEditor.clipboard.dangerouslyPasteHTML(defaultPrologueEn);
        prologueIdEditor.clipboard.dangerouslyPasteHTML(defaultPrologueId);
        closingEnEditor.clipboard.dangerouslyPasteHTML(defaultClosingEn);
        closingIdEditor.clipboard.dangerouslyPasteHTML(defaultClosingId);
        resetSignatureRows();

        policyEditors.splice(0, policyEditors.length);
        document.getElementById('policyPointRows').innerHTML = '';
        policyPointIndex = 0;
        addPolicyPoint({
            titleEn: 'Safety first in all activities and keep a stable Plant operation.',
            descEn: '<p>Maintain plant operational stability by ensuring standards, regulatory compliance, and continuity of raw materials and utilities.</p>',
            titleId: 'Mengutamakan keselamatan dalam semua aktivitas dan menjaga kestabilan operasi pabrik.',
            descId: '<p>Menjaga stabilitas operasional pabrik dengan memastikan standar, kepatuhan regulasi, serta kesinambungan bahan baku dan utilitas.</p>'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        backgroundEnEditor = createEditor('#background_en_editor', defaultBackgroundEn);
        backgroundIdEditor = createEditor('#background_id_editor', defaultBackgroundId);
        prologueEnEditor = createEditor('#prologue_en_editor', defaultPrologueEn);
        prologueIdEditor = createEditor('#prologue_id_editor', defaultPrologueId);
        closingEnEditor = createEditor('#closing_en_editor', defaultClosingEn);
        closingIdEditor = createEditor('#closing_id_editor', defaultClosingId);
        addDefaultBilingualRows();
        resetSignatureRows();
        addPolicyPoint();

        $(document).ready(function() {
            const table = $('#companyPolicyTable').DataTable({
                language: { paginate: { first: '&laquo;&laquo;', previous: '&laquo;', next: '&raquo;', last: '&raquo;&raquo;' } }
            });

            $('#companyPolicyTable tbody').on('click', '.btn-detail', function() {
                const tr = $(this).closest('tr');
                const row = table.row(tr);
                const id = $(this).data('id');
                const html = $('#detail-' + id).html();
                if (row.child.isShown()) { row.child.hide(); tr.removeClass('shown'); }
                else {
                    table.rows().every(function() { if (this.child.isShown()) { this.child.hide(); $(this.node()).removeClass('shown'); } });
                    row.child(html).show(); tr.addClass('shown');
                }
            });

            $('#companyPolicyTable tbody').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({ title: 'Hapus dokumen ini?', text: 'Dokumen dan semua policy point akan dihapus permanen.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' })
                    .then((result) => { if (result.isConfirmed) $('#delete-form-' + id).submit(); });
            });
        });

        document.getElementById('btnAddPolicy').addEventListener('click', resetForm);

        document.querySelectorAll('[data-add-row]').forEach(button => {
            button.addEventListener('click', function() {
                const target = this.dataset.addRow;
                if (target === 'referToRows') addBilingualRow('referToRows', 'refer_to_en', 'refer_to_id');
                if (target === 'consideringRows') addBilingualRow('consideringRows', 'considering_en', 'considering_id');
            });
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                const container = e.target.closest('.card-body').querySelectorAll('.bilingual-row');
                if (container.length <= 1) {
                    Swal.fire({ icon: 'warning', title: 'Minimal 1 row harus tersedia.' });
                    return;
                }
                e.target.closest('.bilingual-row').remove();
            }
            if (e.target.id === 'addSignatureRow') addSignatureRow();
            if (e.target.classList.contains('remove-signature-row')) {
                if (document.querySelectorAll('.signature-col').length <= 1) {
                    Swal.fire({ icon: 'warning', title: 'Minimal 1 tanda tangan harus tersedia.' });
                    return;
                }
                e.target.closest('.signature-col').remove();
                refreshSignatureNumbers();
            }
            if (e.target.id === 'addPolicyPoint') addPolicyPoint();
            if (e.target.classList.contains('remove-policy-point')) {
                const row = e.target.closest('.policy-point');
                if (document.querySelectorAll('.policy-point').length <= 1) {
                    Swal.fire({ icon: 'warning', title: 'Minimal 1 policy point harus tersedia.' });
                    return;
                }
                const index = policyEditors.findIndex(item => item.row === row);
                if (index > -1) policyEditors.splice(index, 1);
                row.remove();
                refreshPolicyNumbers();
            }
        });

        document.getElementById('companyPolicyForm').addEventListener('submit', function(e) {
            if (!document.getElementById('tahun').value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Data belum lengkap', text: 'Silakan pilih Tahun terlebih dahulu.' });
                return;
            }

            document.getElementById('background_en').value = backgroundEnEditor.root.innerHTML;
            document.getElementById('background_id').value = backgroundIdEditor.root.innerHTML;
            document.getElementById('prologue_en').value = prologueEnEditor.root.innerHTML;
            document.getElementById('prologue_id').value = prologueIdEditor.root.innerHTML;
            document.getElementById('closing_en').value = closingEnEditor.root.innerHTML;
            document.getElementById('closing_id').value = closingIdEditor.root.innerHTML;

            const signatureRows = document.querySelectorAll('.signature-col');
            let hasSignature = false;
            signatureRows.forEach(row => {
                const position = row.querySelector('[name="signature_position[]"]').value.trim();
                const name = row.querySelector('[name="signature_name[]"]').value.trim();
                if (position && name) hasSignature = true;
            });

            if (!hasSignature) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Data belum lengkap', text: 'Minimal isi 1 Position dan Name pada Signature.' });
                return;
            }

            let hasPolicy = false;
            policyEditors.forEach(set => {
                const titleEn = set.row.querySelector('.policy-title-en-input');
                const descEn = set.row.querySelector('.policy-desc-en-input');
                const titleId = set.row.querySelector('.policy-title-id-input');
                const descId = set.row.querySelector('.policy-desc-id-input');
                titleEn.value = set.titleEn.root.innerHTML;
                descEn.value = set.descEn.root.innerHTML;
                titleId.value = set.titleId.root.innerHTML;
                descId.value = set.descId.root.innerHTML;
                if (set.titleEn.getText().trim() || set.titleId.getText().trim()) hasPolicy = true;
            });

            if (!hasPolicy) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Data belum lengkap', text: 'Minimal isi satu Policy Point.' });
            }
        });

        $('#companyPolicyTable tbody').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('/company-policy') }}/" + id + "/json",
                type: 'GET',
                success: function(res) {
                    resetForm();
                    $('#companyPolicyModalLabel').text('Edit Company Policy');
                    $('#companyPolicyForm').attr('action', "{{ url('/company-policy') }}/" + id);
                    $('#formMethod').val('PUT');
                    $('#tahun').val(res.tahun || '');
                    if (res.header) {
                        // Backward compatibility with previous structure: keep header in prologue if no separate fields exist.
                        prologueEnEditor.clipboard.dangerouslyPasteHTML(res.prologue_en || res.header || '');
                    }
                    backgroundEnEditor.clipboard.dangerouslyPasteHTML(res.background_en || res.contents_en || '');
                    backgroundIdEditor.clipboard.dangerouslyPasteHTML(res.background_id || res.contents_id || '');
                    prologueEnEditor.clipboard.dangerouslyPasteHTML(res.prologue_en || '');
                    prologueIdEditor.clipboard.dangerouslyPasteHTML(res.prologue_id || '');
                    closingEnEditor.clipboard.dangerouslyPasteHTML(res.closing_en || '');
                    closingIdEditor.clipboard.dangerouslyPasteHTML(res.closing_id || '');

                    policyEditors.splice(0, policyEditors.length);
                    $('#policyPointRows').html('');
                    if (res.details && res.details.length) {
                        res.details.forEach(d => addPolicyPoint({
                            titleEn: d.strategic_goal || '',
                            descEn: d.description || '',
                            titleId: d.strategic_goal_id || '',
                            descId: d.description_id || ''
                        }));
                    } else {
                        addPolicyPoint();
                    }
                    new bootstrap.Modal(document.getElementById('companyPolicyModal')).show();
                }
            });
        });
    });
</script>
@endsection
