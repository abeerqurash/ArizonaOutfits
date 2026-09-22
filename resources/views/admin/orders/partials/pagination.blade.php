@if ($orders->hasPages() || $orders->total() > 0)

    <div class="admin-orders-pagination">

        <div class="admin-orders-pagination-info">
            @if ($orders->total() > 0)
                <span>
                    Page
                    <strong>{{ number_format($orders->currentPage()) }}</strong>
                    of
                    <strong>{{ number_format($orders->lastPage()) }}</strong>
                </span>

                <small>
                    Showing
                    {{ number_format($orders->firstItem()) }}–{{ number_format($orders->lastItem()) }}
                    of {{ number_format($orders->total()) }} orders
                </small>
            @else
                <span>No results</span>
            @endif
        </div>

        @if ($orders->hasPages())
            <nav
                class="admin-orders-pagination-links"
                aria-label="Orders pagination"
            >
                {{-- Previous --}}
                @if ($orders->onFirstPage())
                    <span
                        class="admin-orders-page-button is-disabled"
                        aria-disabled="true"
                        aria-label="Previous page"
                    >
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </span>
                @else
                    <a
                        class="admin-orders-page-button"
                        href="{{ $orders->previousPageUrl() }}"
                        rel="prev"
                        aria-label="Previous page"
                    >
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                @endif

                {{-- Page numbers --}}
                @foreach ($orders->getUrlRange(
                    max(1, $orders->currentPage() - 1),
                    min($orders->lastPage(), $orders->currentPage() + 1)
                ) as $page => $url)
                    @if ($page === $orders->currentPage())
                        <span
                            class="admin-orders-page-button is-active"
                            aria-current="page"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                            class="admin-orders-page-button"
                            href="{{ $url }}"
                            aria-label="Go to page {{ $page }}"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($orders->hasMorePages())
                    <a
                        class="admin-orders-page-button"
                        href="{{ $orders->nextPageUrl() }}"
                        rel="next"
                        aria-label="Next page"
                    >
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span
                        class="admin-orders-page-button is-disabled"
                        aria-disabled="true"
                        aria-label="Next page"
                    >
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </span>
                @endif
            </nav>
        @endif

    </div>

    <style>
        .admin-orders-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 0;
            padding: 14px 18px;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .admin-orders-pagination-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            color: #475569;
            font-size: 12px;
            line-height: 1.4;
        }

        .admin-orders-pagination-info span {
            white-space: nowrap;
        }

        .admin-orders-pagination-info strong {
            color: #0f172a;
            font-weight: 700;
        }

        .admin-orders-pagination-info small {
            color: #94a3b8;
            font-size: 11px;
            white-space: nowrap;
        }

        .admin-orders-pagination-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
            margin: 0;
            padding: 0;
        }

        .admin-orders-page-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            min-width: 32px;
            height: 32px;
            margin: 0;
            padding: 0;
            border: 1px solid #dbe3ee;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            box-sizing: border-box;
            transition:
                border-color .18s ease,
                background-color .18s ease,
                color .18s ease;
        }

        .admin-orders-page-button i {
            font-size: 9px;
            line-height: 1;
        }

        a.admin-orders-page-button:hover {
            border-color: #635bff;
            color: #635bff;
            background: #f7f7ff;
        }

        .admin-orders-page-button.is-active {
            border-color: #635bff;
            background: #635bff;
            color: #ffffff;
        }

        .admin-orders-page-button.is-disabled {
            color: #cbd5e1;
            background: #f8fafc;
            cursor: not-allowed;
        }

        @media (max-width: 700px) {
            .admin-orders-pagination {
                align-items: stretch;
                flex-direction: column;
                padding: 12px 14px;
            }

            .admin-orders-pagination-info {
                justify-content: space-between;
                width: 100%;
            }

            .admin-orders-pagination-links {
                justify-content: center;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .admin-orders-pagination-info small {
                display: none;
            }

            .admin-orders-pagination-info {
                justify-content: center;
            }

            .admin-orders-page-button {
                width: 30px;
                min-width: 30px;
                height: 30px;
            }
        }
    </style>

@endif
