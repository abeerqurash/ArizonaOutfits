@extends('layouts.app')

@section('title', 'Manage Customers')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div class="admin-page-heading">
                    <div>
                        <h1>Manage Customers</h1>
                        <p>View customer accounts, orders and spending.</p>
                    </div>

                    <a href="{{ route('admin.dashboard') }}">
                        Back to Dashboard
                    </a>
                </div>

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="admin-alert admin-alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                <form
                    action="{{ route('admin.customers.index') }}"
                    method="GET"
                    class="admin-filter-form"
                >

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, email or phone"
                    >

                    <select name="status">
                        <option value="">All statuses</option>

                        @foreach ([
                            'active',
                            'inactive',
                            'blocked'
                        ] as $status)

                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>

                        @endforeach
                    </select>

                    <button type="submit">
                        Filter Customers
                    </button>

                    <a href="{{ route('admin.customers.index') }}">
                        Reset
                    </a>

                </form>

                <div class="admin-table-wrapper">

                    <table class="admin-table">

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

                                <tr>
                                    <td>
                                        <strong>
                                            {{ $customer->name }}
                                        </strong>

                                        <br>

                                        <small>
                                            {{ $customer->email }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $customer->phone ?: '—' }}
                                    </td>

                                    <td>
                                        <span class="admin-status admin-status-{{
                                            $customer->status
                                        }}">
                                            {{ ucfirst(
                                                $customer->status
                                                ?: 'active'
                                            ) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ number_format(
                                            $customer->orders_count
                                        ) }}
                                    </td>

                                    <td>
                                        ${{ number_format(
                                            (float) (
                                                $customer->total_spent ?: 0
                                            ),
                                            2
                                        ) }}
                                    </td>

                                    <td>
                                        {{ $customer->created_at?->format(
                                            'M d, Y'
                                        ) }}
                                    </td>

                                    <td>
                                        <a href="{{ route(
                                            'admin.customers.show',
                                            $customer
                                        ) }}">
                                            View
                                        </a>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="7">
                                        No customers were found.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{ $customers->links() }}

            </div>

        </div>
    </div>

</div>

@endsection