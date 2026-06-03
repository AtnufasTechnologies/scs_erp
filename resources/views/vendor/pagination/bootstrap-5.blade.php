<style>
    .modern-pagination-wrapper {
        margin: 2rem 0;
    }

    .modern-pagination-wrapper .pagination-info {
        background: linear-gradient(135deg, #667eea 0%, #543ae6 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .modern-pagination-wrapper .pagination-info i {
        font-size: 16px;
    }

    .modern-pagination-wrapper .pagination {
        gap: 8px;
        margin: 0;
    }

    .modern-pagination-wrapper .page-item {
        margin: 0;
    }

    .modern-pagination-wrapper .page-link {
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        padding: 10px 16px;
        color: #475569;
        font-weight: 600;
        transition: all 0.3s ease;
        background: white;
        min-width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .modern-pagination-wrapper .page-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #543ae6 100%);
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
    }

    .modern-pagination-wrapper .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #543ae6 100%);
        border-color: #667eea;
        color: white;
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        transform: scale(1.05);
    }

    .modern-pagination-wrapper .page-item.disabled .page-link {
        background: #f1f5f9;
        border-color: #e1e8ed;
        color: #cbd5e1;
        cursor: not-allowed;
        box-shadow: none;
    }

    .modern-pagination-wrapper .page-item.disabled .page-link:hover {
        transform: none;
        background: #f1f5f9;
        color: #cbd5e1;
    }

    .modern-pagination-wrapper .pagination-nav-btn {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .modern-pagination-wrapper .pagination-nav-btn i {
        font-size: 12px;
    }

    /* Mobile responsive */
    @media (max-width: 576px) {
        .modern-pagination-wrapper .pagination-info {
            font-size: 12px;
            padding: 10px 16px;
        }

        .modern-pagination-wrapper .page-link {
            min-width: 38px;
            height: 38px;
            padding: 8px 12px;
            font-size: 14px;
        }
    }
</style>

@if ($paginator->hasPages())
<nav class="modern-pagination-wrapper">
    <!-- Mobile View -->
    <div class="d-flex justify-content-between align-items-center flex-fill d-sm-none mb-3">
        <span class="pagination-info">
            <i class="fas fa-file-alt"></i>
            <span>{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        </span>
    </div>
    <div class="d-flex justify-content-between flex-fill d-sm-none">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link pagination-nav-btn">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link pagination-nav-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </a>
            </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link pagination-nav-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link pagination-nav-btn">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
            @endif
        </ul>
    </div>

    <!-- Desktop View -->
    <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
        <div>
            <span class="pagination-info">
                <i class="fas fa-file-alt"></i>
                <span>
                    Showing <strong>{{ $paginator->firstItem() }}</strong> to
                    <strong>{{ $paginator->lastItem() }}</strong> of
                    <strong>{{ $paginator->total() }}</strong> results
                </span>
            </span>
        </div>

        <div>
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{{ $element }}</span>
                </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $page }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@endif