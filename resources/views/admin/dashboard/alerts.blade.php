<section class="admin-dashboard-alert-centre">

    <div class="admin-dashboard-alert-heading">

        <div>
            <span class="admin-page-eyebrow">
                Attention required
            </span>

            <h3>
                Dashboard Alerts
            </h3>

            <p>
                Important store issues that may require administrator action.
            </p>
        </div>

        <div class="admin-alert-summary">

            <span>
                <strong>
                    {{ number_format($dashboardAlertSummary['total']) }}
                </strong>

                Total
            </span>

            @if ($dashboardAlertSummary['critical'] > 0)

                <span class="admin-alert-summary-critical">
                    <strong>
                        {{
                            number_format(
                                $dashboardAlertSummary['critical']
                            )
                        }}
                    </strong>

                    Critical
                </span>

            @endif

        </div>

    </div>

    @if (count($dashboardAlerts) > 0)

        <div class="admin-dashboard-alert-list">

            @foreach ($dashboardAlerts as $alert)

                <article
                    class="
                        admin-dashboard-alert
                        admin-dashboard-alert-{{ $alert['type'] }}
                    "
                >

                    <span class="admin-dashboard-alert-icon">
                        <i class="{{ $alert['icon'] }}"></i>
                    </span>

                    <div class="admin-dashboard-alert-content">

                        <strong>
                            {{ $alert['title'] }}
                        </strong>

                        <p>
                            {{ $alert['message'] }}
                        </p>

                    </div>

                    <a href="{{ $alert['action_url'] }}">
                        {{ $alert['action_label'] }}

                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </article>

            @endforeach

        </div>

    @else

        <div class="admin-dashboard-all-clear">

            <span>
                <i class="fa-solid fa-circle-check"></i>
            </span>

            <div>
                <strong>
                    Everything looks good
                </strong>

                <p>
                    There are currently no store issues requiring your
                    attention.
                </p>
            </div>

        </div>

    @endif

</section>

<style>
    .admin-dashboard-alert-centre {
        margin-bottom: 24px;
        padding: 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
    }

    .admin-dashboard-alert-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 20px;
    }

    .admin-dashboard-alert-heading h3 {
        margin: 5px 0 0;
        color: #0f172a;
        font-size: 20px;
    }

    .admin-dashboard-alert-heading p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .admin-alert-summary {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-alert-summary span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        color: #475569;
        background: #f1f5f9;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .admin-alert-summary strong {
        color: #0f172a;
    }

    .admin-alert-summary .admin-alert-summary-critical {
        color: #b91c1c;
        background: #fee2e2;
    }

    .admin-alert-summary-critical strong {
        color: #991b1b;
    }

    .admin-dashboard-alert-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .admin-dashboard-alert {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 15px;
        padding: 16px;
        border: 1px solid transparent;
        border-radius: 12px;
    }

    .admin-dashboard-alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 11px;
        font-size: 17px;
    }

    .admin-dashboard-alert-content {
        min-width: 0;
    }

    .admin-dashboard-alert-content strong {
        display: block;
        margin-bottom: 4px;
        color: #0f172a;
        font-size: 14px;
    }

    .admin-dashboard-alert-content p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    .admin-dashboard-alert > a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .admin-dashboard-alert > a:hover {
        text-decoration: underline;
    }

    .admin-dashboard-alert-danger {
        background: #fff7f7;
        border-color: #fecaca;
    }

    .admin-dashboard-alert-danger
    .admin-dashboard-alert-icon {
        color: #b91c1c;
        background: #fee2e2;
    }

    .admin-dashboard-alert-warning {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .admin-dashboard-alert-warning
    .admin-dashboard-alert-icon {
        color: #b45309;
        background: #fef3c7;
    }

    .admin-dashboard-alert-info {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .admin-dashboard-alert-info
    .admin-dashboard-alert-icon {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .admin-dashboard-alert-notice {
        background: #faf5ff;
        border-color: #e9d5ff;
    }

    .admin-dashboard-alert-notice
    .admin-dashboard-alert-icon {
        color: #7e22ce;
        background: #f3e8ff;
    }

    .admin-dashboard-all-clear {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 18px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
    }

    .admin-dashboard-all-clear > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        color: #15803d;
        background: #dcfce7;
        border-radius: 50%;
        font-size: 18px;
    }

    .admin-dashboard-all-clear strong {
        display: block;
        margin-bottom: 4px;
        color: #166534;
        font-size: 14px;
    }

    .admin-dashboard-all-clear p {
        margin: 0;
        color: #4d7c5a;
        font-size: 13px;
    }

    @media (max-width: 800px) {
        .admin-dashboard-alert-heading {
            flex-direction: column;
        }

        .admin-dashboard-alert {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .admin-dashboard-alert > a {
            grid-column: 2;
            justify-self: flex-start;
        }
    }

    @media (max-width: 520px) {
        .admin-dashboard-alert-centre {
            padding: 16px;
        }

        .admin-alert-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .admin-dashboard-alert {
            grid-template-columns: 1fr;
        }

        .admin-dashboard-alert-icon {
            width: 38px;
            height: 38px;
        }

        .admin-dashboard-alert > a {
            grid-column: 1;
        }
    }
</style>