@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Create Coupon</h1>

                    <form action="{{ route('admin.coupons.store') }}" method="POST">
                        @csrf

                        <input type="text" name="code" placeholder="Coupon Code" required>
                        <br><br>

                        <select name="type" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                        <br><br>

                        <input type="number" step="0.01" name="value" placeholder="Value" required>
                        <br><br>

                        <input type="number" step="0.01" name="minimum_order_amount" placeholder="Minimum Order Amount">
                        <br><br>

                        <input type="number" name="usage_limit" placeholder="Usage Limit">
                        <br><br>

                        <label>Start Date</label>
                        <input type="date" name="start_date">
                        <br><br>

                        <label>End Date</label>
                        <input type="date" name="end_date">
                        <br><br>

                        <label>
                            <input type="checkbox" name="status" value="1" checked>
                            Active
                        </label>
                        <br><br>

                        <button type="submit">Save Coupon</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection