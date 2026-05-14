@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div class="muted" style="font-size:13px;">
            Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            @if ($paginator->onFirstPage())
                <span class="btn" style="opacity:.55;cursor:not-allowed;">Prev</span>
            @else
                <a class="btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="btn" style="opacity:.55;cursor:not-allowed;">Next</span>
            @endif
        </div>
    </nav>
@endif
