@forelse ($policy?->details ?? [] as $detail)
    @if ($detail->strategic_goal_id)
        <td>
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="h-50px w-50px d-flex justify-content-center align-items-center bg-secondary text-white fs-4 rounded-pill mb-3">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <h6 class="fw-semibold fs-14 mb-1">{!! $detail->strategic_goal_id !!}</h6>
                <p class="text-muted fs-13 mb-0">{!! $detail->description_id !!}</p>
            </div>
        </td>
    @endif
@empty
    <td class="text-muted py-4">No strategic goals for selected year.</td>
@endforelse
