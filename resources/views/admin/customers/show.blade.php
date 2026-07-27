@extends('layouts.app')

@section('title', 'Manage Customer')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div class="admin-page-heading">
                    <div>
                        <h1>{{ $customer->name }}</h1>
                        <p>{{ $customer->email }}</p>
                    </div>

                    <a href="{{ route('admin.customers.index') }}">
                        Back to Customers
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

                @if ($errors->any())
                    <div class="admin-alert admin-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="dashboard-statistics">

                    <div class="dashboard-card">
                        <h3>Total Orders</h3>
                        <strong>
                            {{ number_format(
                                $customer->orders->count()
                            ) }}
                        </strong>
                    </div>

                    <div class="dashboard-card">
                        <h3>Total Spent</h3>
                        <strong>
                            ${{ number_format(
                                (float) $totalSpent,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="dashboard-card">
                        <h3>Reviews</h3>
                        <strong>
                            {{ number_format(
                                $customer->reviews->count()
                            ) }}
                        </strong>
                    </div>

                </div>

                <section class="admin-panel">

                    <h2>Edit Customer</h2>

                    <form
                        action="{{ route(
                            'admin.customers.update',
                            $customer
                        ) }}"
                        method="POST"
                        class="admin-form"
                    >
                        @csrf
                        @method('PUT')

                        <label for="name">Name</label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old(
                                'name',
                                $customer->name
                            ) }}"
                            required
                        >

                        <label for="email">Email</label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old(
                                'email',
                                $customer->email
                            ) }}"
                            required
                        >

                        <label for="phone">Phone</label>

                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old(
                                'phone',
                                $customer->phone
                            ) }}"
                        >

                        <label for="status">
                            Account Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            required
                        >
                            @foreach ([
                                'active',
                                'inactive',
                                'blocked'
                            ] as $status)

                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        old(
                                            'status',
                                            $customer->status
                                        ) === $status
                                    )
                                >
                                    {{ ucfirst($status) }}
                                </option>

                            @endforeach
                        </select>

                        <button type="submit">
                            Update Customer
                        </button>

                    </form>

                </section>

                <section class="admin-section">

                    <h2>Customer Orders</h2>

                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($customer->orders as $order)

                                    <tr>
                                        <td>
                                            {{ $order->order_number
                                                ?: '#' . $order->id }}
                                        </td>

                                        <td>
                                            ${{ number_format(
                                                (float) $order->total,
                                                2
                                            ) }}
                                        </td>

                                        <td>
                                            {{ ucfirst(
                                                $order->payment_status
                                                ?: 'pending'
                                            ) }}
                                        </td>

                                        <td>
                                            {{ ucfirst(
                                                $order->order_status
                                                ?: 'pending'
                                            ) }}
                                        </td>

                                        <td>
                                            {{ $order->created_at?->format(
                                                'M d, Y'
                                            ) }}
                                        </td>

                                        <td>
                                            <a href="{{ route(
                                                'admin.orders.show',
                                                $order
                                            ) }}">
                                                View
                                            </a>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6">
                                            This customer has no orders.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </section>

                <form
                    action="{{ route(
                        'admin.customers.destroy',
                        $customer
                    ) }}"
                    method="POST"
                    onsubmit="return confirm('Delete this customer permanently?');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-danger-button"
                    >
                        Delete Customer
                    </button>
                </form>

            </div>

        </div>
    </div>

</div>

@endsection