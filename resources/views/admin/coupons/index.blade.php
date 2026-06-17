@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Coupons</h1>

                    <a href="{{ route('admin.coupons.create') }}">Add Coupon</a>

                    @if(session('success'))
                        <p>{{ session('success') }}</p>
                    @endif

                    <table border="1" cellpadding="10">
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Order</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                        @foreach($coupons as $coupon)
                            <tr>
                                <td>{{ $coupon->code }}</td>
                                <td>{{ $coupon->type }}</td>
                                <td>{{ $coupon->value }}</td>
                                <td>{{ $coupon->minimum_order_amount ?? '-' }}</td>
                                <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                                <td>{{ $coupon->status ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}">Edit</a>

                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this coupon?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{ $coupons->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection