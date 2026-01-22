@extends('layouts.master')

@section('title', 'Budget Verification | Budget Control')
@section('title-sub', 'Budget Verification')
@section('pagetitle', 'Budget Verification - Verify Budget Items')

@section('css')
    <style>
        .verification-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
            transition: box-shadow 0.2s;
        }
        .verification-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .verification-card.pending {
            border-left-color: #ffc107;
        }
        .price-input-group {
            max-width: 300px;
        }
        .price-input-group .form-control {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .item-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }
        .item-details dt {
            font-weight: 500;
            color: #6c757d;
        }
        .item-details dd {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .badge-estimation {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Budget Items Pending Verification
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadPendingItems()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="pendingItemsContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading pending items...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Verify Item --}}
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="verifyModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Verify Budget Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="verifyItemDetails" class="item-details mb-4"></div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">
                            <strong>Price Estimation (User Input)</strong>
                        </label>
                        <div id="verifyPriceEstimation" class="badge bg-secondary badge-estimation">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="verifyFixPrice">
                            <strong>Verified Price (Fix Price) <span class="text-danger">*</span></strong>
                        </label>
                        <div class="input-group price-input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="verifyFixPrice" 
                                   placeholder="Enter verified price" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label" for="verifyNotes">Notes (Optional)</label>
                    <textarea class="form-control" id="verifyNotes" rows="3" 
                              placeholder="Add notes about the price verification..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmVerify()">
                    <i class="bi bi-check-lg me-1"></i>Verify & Submit for Approval
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reject Item --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2"></i>Reject Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rejectItemDetails" class="item-details mb-4"></div>
                
                <div class="mt-3">
                    <label class="form-label" for="rejectNotes">
                        <strong>Rejection Reason <span class="text-danger">*</span></strong>
                    </label>
                    <textarea class="form-control" id="rejectNotes" rows="4" 
                              placeholder="Enter the reason for rejection..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-x-lg me-1"></i>Reject
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center text-white">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Processing...</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('js')
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    let currentItemId = null;
    let currentItemData = null;
    let pendingItems = [];

    $(document).ready(function() {
        loadPendingItems();
        
        // Format price input
        $('#verifyFixPrice').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value) {
                $(this).val(formatNumber(parseInt(value)));
            }
        });
    });

    /**
     * Load pending verification items for current user
     */
    function loadPendingItems() {
        $('#pendingItemsContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading pending items...</p>
            </div>
        `);

        $.ajax({
            url: '/budget-verification/pending',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    pendingItems = response.data;
                    renderPendingItems(response.data);
                } else {
                    renderEmptyState();
                }
            },
            error: function(xhr) {
                console.error('Error loading pending items:', xhr);
                $('#pendingItemsContainer').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load pending items. Please try again.
                    </div>
                `);
            }
        });
    }

    /**
     * Render pending items
     */
    function renderPendingItems(items) {
        let html = '';
        
        items.forEach(item => {
            html += `
                <div class="card verification-card pending" data-item-id="${item.id}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="card-title mb-3">
                                    <span class="badge bg-warning text-dark me-2">Pending</span>
                                    ${item.description || 'No description'}
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Cost Center</small>
                                        <div class="fw-semibold">${item.cost_center || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Category</small>
                                        <div class="fw-semibold">${item.category?.name || item.category_type || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Workplan</small>
                                        <div class="fw-semibold">${item.workplan?.activity || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <small class="text-muted d-block">Price Estimation</small>
                                <h4 class="text-primary mb-3">${formatCurrency(item.price_estimation || 0)}</h4>
                                <div class="action-buttons justify-content-md-end">
                                    <button type="button" class="btn btn-success btn-sm" onclick="openVerifyModal(${item.id})">
                                        <i class="bi bi-check-lg me-1"></i>Verify
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal(${item.id})">
                                        <i class="bi bi-x-lg me-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#pendingItemsContainer').html(html);
    }

    /**
     * Render empty state
     */
    function renderEmptyState() {
        $('#pendingItemsContainer').html(`
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Pending Verifications</h5>
                <p class="text-muted">You don't have any budget items pending verification.</p>
            </div>
        `);
    }

    /**
     * Open verify modal
     */
    function openVerifyModal(itemId) {
        currentItemId = itemId;
        currentItemData = pendingItems.find(i => i.id === itemId);
        
        if (!currentItemData) {
            showToast('Item not found', 'error');
            return;
        }

        // Populate item details
        $('#verifyItemDetails').html(`
            <dl class="row mb-0">
                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">${currentItemData.description || '-'}</dd>
                
                <dt class="col-sm-4">Cost Center</dt>
                <dd class="col-sm-8">${currentItemData.cost_center || '-'}</dd>
                
                <dt class="col-sm-4">Category</dt>
                <dd class="col-sm-8">${currentItemData.category?.name || currentItemData.category_type || '-'}</dd>
                
                <dt class="col-sm-4">Workplan</dt>
                <dd class="col-sm-8">${currentItemData.workplan?.activity || '-'}</dd>
            </dl>
        `);

        $('#verifyPriceEstimation').text(formatCurrency(currentItemData.price_estimation || 0));
        $('#verifyFixPrice').val(formatNumber(currentItemData.price_estimation || 0));
        $('#verifyNotes').val('');

        $('#verifyModal').modal('show');
    }

    /**
     * Open reject modal
     */
    function openRejectModal(itemId) {
        currentItemId = itemId;
        currentItemData = pendingItems.find(i => i.id === itemId);
        
        if (!currentItemData) {
            showToast('Item not found', 'error');
            return;
        }

        // Populate item details
        $('#rejectItemDetails').html(`
            <dl class="row mb-0">
                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">${currentItemData.description || '-'}</dd>
                
                <dt class="col-sm-4">Cost Center</dt>
                <dd class="col-sm-8">${currentItemData.cost_center || '-'}</dd>
                
                <dt class="col-sm-4">Price Estimation</dt>
                <dd class="col-sm-8">${formatCurrency(currentItemData.price_estimation || 0)}</dd>
            </dl>
        `);

        $('#rejectNotes').val('');

        $('#rejectModal').modal('show');
    }

    /**
     * Confirm verify
     */
    function confirmVerify() {
        const fixPriceStr = $('#verifyFixPrice').val().replace(/[^0-9]/g, '');
        const fixPrice = parseFloat(fixPriceStr);
        const notes = $('#verifyNotes').val();

        if (!fixPrice || fixPrice <= 0) {
            showToast('Please enter a valid verified price', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Verification',
            html: `
                <p>You are about to verify this budget item with:</p>
                <h4 class="text-success">${formatCurrency(fixPrice)}</h4>
                <p class="text-muted">The item will be automatically submitted for approval.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Verify & Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                processVerification(fixPrice, notes);
            }
        });
    }

    /**
     * Process verification
     */
    function processVerification(fixPrice, notes) {
        showLoading();

        $.ajax({
            url: `/budget-verification/${currentItemId}/verify`,
            method: 'POST',
            data: {
                _token: CSRF_TOKEN,
                fix_price: fixPrice,
                notes: notes
            },
            success: function(response) {
                hideLoading();
                $('#verifyModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadPendingItems();
                } else {
                    showToast(response.message || 'Failed to verify', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                const msg = xhr.responseJSON?.message || 'Error processing verification';
                showToast(msg, 'error');
            }
        });
    }

    /**
     * Confirm reject
     */
    function confirmReject() {
        const notes = $('#rejectNotes').val().trim();

        if (!notes) {
            showToast('Please enter a rejection reason', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Rejection',
            text: 'Are you sure you want to reject this budget item verification?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                processRejection(notes);
            }
        });
    }

    /**
     * Process rejection
     */
    function processRejection(notes) {
        showLoading();

        $.ajax({
            url: `/budget-verification/${currentItemId}/reject`,
            method: 'POST',
            data: {
                _token: CSRF_TOKEN,
                notes: notes
            },
            success: function(response) {
                hideLoading();
                $('#rejectModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadPendingItems();
                } else {
                    showToast(response.message || 'Failed to reject', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                const msg = xhr.responseJSON?.message || 'Error processing rejection';
                showToast(msg, 'error');
            }
        });
    }

    /**
     * Format currency
     */
    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    /**
     * Format number with thousand separator
     */
    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }

    /**
     * Show loading overlay
     */
    function showLoading() {
        $('#loadingOverlay').css('display', 'flex');
    }

    /**
     * Hide loading overlay
     */
    function hideLoading() {
        $('#loadingOverlay').css('display', 'none');
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }
</script>
@endsection
