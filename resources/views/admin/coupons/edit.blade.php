@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Edit Coupon</h1>

                    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="text" name="code" value="{{ $coupon->code }}" required>
                        <br><br>

                        <select name="type" required>
                            <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>Percentage
                            </option>
                        </select>
                        <br><br>

                        <input type="number" step="0.01" name="value" value="{{ $coupon->value }}" required>
                        <br><br>

                        <input type="number" step="0.01" name="minimum_order_amount"
                            value="{{ $coupon->minimum_order_amount }}" placeholder="Minimum Order Amount">
                        <br><br>

                        <input type="number" name="usage_limit" value="{{ $coupon->usage_limit }}"
                            placeholder="Usage Limit">
                        <br><br>

                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ $coupon->start_date }}">
                        <br><br>

                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ $coupon->end_date }}">
                        <br><br>

                        <label>
                            <input type="checkbox" name="status" value="1" {{ $coupon->status ? 'checked' : '' }}>
                            Active
                        </label>
                        <br><br>

                        <button type="submit">Update Coupon</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection