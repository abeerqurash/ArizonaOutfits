@extends('admin.layouts.app')

@section('title', 'Manage Customers')
@section('page-heading', 'Customers')

@section('content')
<div class="admin-customers-page">
    <div class="admin-page-header">
        <div>
            <span class="admin-page-eyebrow">Customer management</span>
            <h2>Manage Customers</h2>
            <p>Search customer accounts, review activity and manage account status.</p>
        </div>

        <div class="admin-page-actions">
            <a href="{{ route('admin.dashboard') }}" class="admin-button admin-button-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
    @endif

    <section class="admin-panel">
        <div class="admin-panel-header">
            <div>
                <span class="admin-panel-eyebrow">Customer accounts</span>
                <h3>All Customers</h3>
                <p class="admin-customers-summary">
                    @if ($customers->total() > 0)
                        Showing {{ number_format($customers->firstItem()) }}–{{ number_format($customers->lastItem()) }}
                        of {{ number_format($customers->total()) }} customers
                    @else
                        No customers found
                    @endif
                </p>
            </div>
        </div>

        <form action="{{ route('admin.customers.index') }}" method="GET" class="admin-customers-filter">
            <div class="admin-customers-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email or phone"
                    aria-label="Search customers"
                >
            </div>

            <select name="status" aria-label="Filter by account status">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive', 'blocked'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <button type="submit">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>

            <a href="{{ route('admin.customers.index') }}">
                <i class="fa-solid fa-rotate-left"></i>
                Reset
            </a>
        </form>

        <div class="admin-customers-table-wrapper">
            <table class="admin-table admin-customers-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $status = $customer->status ?: 'active';
                        @endphp

                        <tr>
                            <td class="admin-customers-identity">
                                <strong>{{ $customer->name ?: 'Unnamed customer' }}</strong>
                                <small>{{ $customer->email ?: 'No email on profile' }}</small>
                            </td>

                            <td>{{ $customer->phone ?: '—' }}</td>

                            <td>
                                <span class="admin-customer-status admin-customer-status-{{ $status }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>{{ number_format($customer->orders_count) }}</td>

                            <td class="admin-customers-money">
                                {{ \App\Services\CustomerSpendService::format($customer->paid_spend_by_currency ?? []) }}
                            </td>

                            <td class="admin-customers-date">
                                {{ $customer->created_at?->format('M d, Y') ?: '—' }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.customers.show', $customer) }}"
                                    class="admin-customers-view-button"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-customers-empty">
                                <div class="admin-customers-empty-state">
                                    <span>
                                        <i class="fa-solid fa-users"></i>
                                    </span>
                                    <strong>No customers found</strong>
                                    <small>Try changing your search or status filter.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="admin-customers-pagination">
                {{ $customers->links() }}
            </div>
        @endif
    </section>
</div>

<style>
.admin-customers-summary { margin: 6px 0 0; color: #64748b; font-size: 14px; }
.admin-customers-filter { display: grid; grid-template-columns: minmax(260px, 2fr) minmax(170px, 1fr) auto auto; gap: 12px; padding: 20px 24px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.admin-customers-search { position: relative; }
.admin-customers-search > i { position: absolute; top: 50%; left: 14px; color: #94a3b8; transform: translateY(-50%); pointer-events: none; }
.admin-customers-search input { padding-left: 40px !important; }
.admin-customers-filter input, .admin-customers-filter select { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; color: #0f172a; font: inherit; }
.admin-customers-filter button, .admin-customers-filter a { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 44px; padding: 10px 15px; border-radius: 9px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; cursor: pointer; }
.admin-customers-filter button { border: 1px solid #0f172a; background: #0f172a; color: #fff; }
.admin-customers-filter a { border: 1px solid #cbd5e1; background: #fff; color: #334155; }
.admin-customers-table-wrapper { overflow-x: auto; }
.admin-customers-identity { min-width: 220px; }
.admin-customers-identity strong, .admin-customers-identity small { display: block; }
.admin-customers-identity small { margin-top: 4px; color: #64748b; }
.admin-customers-money { white-space: nowrap; font-weight: 600; }
.admin-customers-date { min-width: 120px; white-space: nowrap; }
.admin-customer-status { display: inline-flex; align-items: center; padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
.admin-customer-status-active { background: #dcfce7; color: #166534; }
.admin-customer-status-inactive { background: #f1f5f9; color: #475569; }
.admin-customer-status-blocked { background: #fee2e2; color: #991b1b; }
.admin-customers-view-button { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; }
.admin-customers-empty { padding: 48px 20px !important; text-align: center; }
.admin-customers-empty-state { display: flex; flex-direction: column; align-items: center; gap: 7px; color: #64748b; }
.admin-customers-empty-state span { display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; margin-bottom: 6px; border-radius: 50%; background: #f1f5f9; font-size: 22px; }
.admin-customers-empty-state strong { color: #334155; }
.admin-customers-pagination { padding: 18px 24px; border-top: 1px solid #e2e8f0; }
@media (max-width: 850px) { .admin-customers-filter { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .admin-customers-filter { grid-template-columns: 1fr; padding: 16px; } }
</style>
@endsection
