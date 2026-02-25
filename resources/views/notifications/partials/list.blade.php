@forelse($notifications as $notification)
    @php
        $isRead = $notification->reads()->where('employee_id', $employeeId)->where('is_read', true)->exists();
    @endphp
    <div class="dropdown-item d-flex align-items-start p-3 border-bottom {{ $isRead ? 'bg-light' : 'bg-white fw-bold border-start border-primary border-4' }} notification-item" 
         data-id="{{ $notification->id }}" style="cursor: pointer;">
        <div class="flex-shrink-0 me-3">
            <div class="avatar-sm">
                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16">
                    <i class="{{ $notification->category->icon ?? 'bi bi-bell' }}"></i>
                </span>
            </div>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="mb-0 fs-14 {{ $isRead ? 'text-muted' : 'text-dark' }}">{{ $notification->title }}</h6>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-0 fs-12 text-muted text-truncate" style="max-width: 250px;">
                {{ $notification->details }}
            </p>
        </div>
    </div>
@empty
    <div class="p-4 text-center">
        <p class="text-muted mb-0">No notifications found.</p>
    </div>
@endforelse
