<div class="admin-table-wrapper admin-orders-table-wrapper">

    <table class="admin-table admin-orders-table">

        <thead>
            <tr>
                <th class="admin-orders-checkbox-column">
                    <input
                        type="checkbox"
                        id="ordersSelectAll"
                        class="admin-order-checkbox"
                        aria-label="Select all orders on this page"
                    >
                </th>

                <th>Order</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Tracking</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @include('admin.orders.partials.rows', [
                'orders' => $orders,
            ])
        </tbody>

    </table>

</div>