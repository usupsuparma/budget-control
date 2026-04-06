@extends('layouts.master')

@section('title', 'Import Workplan Budget | Budget Control')
@section('title-sub', 'Import CSV')
@section('pagetitle', 'Budget Control')

@section('content')
<div id="layout-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-cloud-upload text-primary me-2"></i> Import Workplan & Budget Items
                    </h5>
                    <p class="text-muted fs-13 mt-1 mb-0">Unggah file CSV dengan standar format data anggaran untuk inisialisasi awal target perusahaan.</p>
                </div>
                <div class="card-body p-4">
                    
                    {{-- config data for ajax --}}
                    <div id="app-config" data-urls="{{ json_encode(['import' => route('import.workplan-budget')]) }}" class="d-none"></div>

                    <form id="form-import-csv" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="csvBase" class="form-label fw-medium">Pilih File CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-lg" id="csvBase" name="file" accept=".csv, .txt" required>
                            <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Ekstensi yang dibolehkan: .csv. Maksimal size 20MB.</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit-import">
                                <i class="bi bi-upload me-2"></i> Mulai Proses Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Peringatan Penting!</h6>
                    <p class="mb-0 fs-13">Proses ini akan meng-generate sturktur KPI dan inisialisasi anggaran. Pastikan file tidak ganda agar tidak terjadi double input terhadap sistem.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    const urls = $('#app-config').data('urls');

    $('#form-import-csv').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let fileInput = $('#csvBase')[0];
        
        if (fileInput.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harap pilih file CSV terlebih dahulu!'
            });
            return;
        }

        $.ajax({
            url: urls.import,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_csrf"]').val() || $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#btn-submit-import').prop('disabled', true);
                Swal.fire({
                    title: 'Memproses Data...',
                    text: 'Mohon tunggu, jangan tutup halaman ini.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                $('#btn-submit-import').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `<b>${response.message}</b><br>
                               <small>Processed: ${response.data.processed} rows</small><br>
                               <small>Skipped: ${response.data.skipped} rows</small>
                               `
                    }).then(() => {
                        $('#form-import-csv')[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups, Gagal',
                        text: response.message || 'Terjadi kesalahan sistem.'
                    });
                }
            },
            error: function(xhr) {
                $('#btn-submit-import').prop('disabled', false);
                
                let errorMsg = 'Terjadi kesalahan saat memproses permintaan Anda.';
                if (xhr.status === 422) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Extract first validation error
                        const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                        errorMsg = firstError;
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMsg
                });
            }
        });
    });
});
</script>
@endsection
