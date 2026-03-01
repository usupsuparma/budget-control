@extends('layouts.master')

@section('title', 'Notifications | Budget Control System')
@section('title-sub', 'User')
@section('pagetitle', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-bell me-2"></i>All Notifications</h5>
                    <button id="markAllReadPage" class="btn btn-sm btn-outline-primary">Mark All as Read</button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($notifications as $notif)
                        <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-bottom notification-page-item" 
                             data-id="{{ $notif->id }}" style="cursor: pointer;">
                            <div class="flex-shrink-0">
                                @php
                                    $bgColor = 'bg-primary';
                                    $icon = 'bi-info-circle';
                                    if($notif->category) {
                                        switch(strtolower($notif->category->name)) {
                                            case 'approval': $bgColor = 'bg-warning'; $icon = 'bi-check2-circle'; break;
                                            case 'system': $bgColor = 'bg-danger'; $icon = 'bi-cpu'; break;
                                            case 'info': $bgColor = 'bg-info'; $icon = 'bi-info-lg'; break;
                                        }
                                    }
                                @endphp
                                <div class="h-44px w-44px d-flex justify-content-center align-items-center rounded-circle {{ $bgColor }} text-white shadow-sm">
                                    <i class="bi {{ $icon }} fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 {{ !$notif->is_read ? 'fw-bold text-dark' : 'text-muted' }}">{{ $notif->title }}</h6>
                                    <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 {{ !$notif->is_read ? 'text-dark' : 'text-muted' }} fs-14">{{ $notif->details }}</p>
                            </div>
                            @if(!$notif->is_read)
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill p-1"><span class="visually-hidden">New</span></span>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="p-5 text-center">
                            <i class="bi bi-bell-slash fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">You don't have any notifications yet.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($notifications->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $notifications->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Mark as read when clicking on a notification item
    $('.notification-page-item').on('click', function() {
        var id = $(this).data('id');
        var $this = $(this);
        
        $.ajax({
            url: "{{ url('notifications/mark-as-read') }}/" + id,
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                $this.find('h6').removeClass('fw-bold text-dark').addClass('text-muted');
                $this.find('p').removeClass('text-dark').addClass('text-muted');
                $this.find('.badge').remove();
                
                // Update header notification count if function exists
                if(typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            }
        });
    });

    // Mark all as read
    $('#markAllReadPage').on('click', function() {
        $.ajax({
            url: "{{ route('notifications.readAll') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                $('.notification-page-item h6').removeClass('fw-bold text-dark').addClass('text-muted');
                $('.notification-page-item p').removeClass('text-dark').addClass('text-muted');
                $('.notification-page-item .badge').remove();
                
                if(typeof loadNotifications === 'function') {
                    loadNotifications();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'All notifications marked as read',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
});
</script>
@endsection
