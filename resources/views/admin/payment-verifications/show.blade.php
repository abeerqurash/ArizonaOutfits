@extends('admin.layouts.app')

@section('title', 'Payment Verification')
@section('page-heading', 'Payment Verification')

@section('content')
@php
    $provider = (string) ($order->payment_provider ?: $order->payment_method ?: 'unknown');
    $status = strtolower((string) ($order->payment_status ?: 'pending'));
    $isBank = $provider === 'bank_transfer';

    $metadata = $order->payment_metadata;
    if (is_string($metadata)) {
        $decoded = json_decode($metadata, true);
        $metadata = is_array($decoded) ? $decoded : [];
    }
    $metadata = is_array($metadata) ? $metadata : [];
    $verification = $metadata['bank_transfer_verification'] ?? [];

    $verified = $isBank
        ? !empty($verification['verified'])
        : in_array($status, ['paid', 'completed', 'succeeded'], true);

    $rejected = $isBank && (
        !empty($verification['rejected_at'])
        || in_array($status, ['failed', 'declined', 'cancelled'], true)
    );

    $verificationLabel = $verified ? 'Verified' : ($rejected ? 'Rejected' : 'Pending Review');
    $verificationClass = $verified ? 'verified' : ($rejected ? 'rejected' : 'pending');

    $customerName = $order->billing_name
        ?: $order->shipping_name
        ?: $order->user?->name
        ?: 'Customer';

    $customerEmail = $order->billing_email
        ?: $order->shipping_email
        ?: $order->user?->email
        ?: '—';

    $providerLabel = ucwords(str_replace('_', ' ', $provider));
    $providerIcon = match ($provider) {
        'stripe' => 'fa-brands fa-stripe-s',
        'bank_transfer' => 'fa-solid fa-building-columns',
        default => 'fa-solid fa-wallet',
    };

    $transactionId = $order->payment_intent_id
        ?: ($metadata['payment_intent_id'] ?? null)
        ?: ($metadata['transaction_id'] ?? null)
        ?: ($metadata['payment_id'] ?? null);

    $reference = $order->payment_reference
        ?: ($isBank ? $order->order_number : null);
@endphp

<div class="admin-page-header pv-header">
    <div>
        <span class="admin-page-eyebrow">PAYMENT OPERATIONS</span>
        <h2>Payment Verification</h2>
        <p>Review payment details for order <strong>{{ $order->order_number ?: '#' . $order->id }}</strong>.</p>
    </div>

    <div class="admin-page-actions">
        <a href="{{ route('admin.payment-verifications.index') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Payments
        </a>
        <a href="{{ route('admin.orders.show', $order) }}" class="admin-button admin-button-primary">
            <i class="fa-solid fa-bag-shopping"></i> View Order
        </a>
    </div>
</div>

@if(session('success'))
<div class="pv-alert success"><i class="fa-solid fa-circle-check"></i><div><strong>Success</strong><p>{{ session('success') }}</p></div></div>
@endif

@if(session('error'))
<div class="pv-alert error"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Action required</strong><p>{{ session('error') }}</p></div></div>
@endif

@if($errors->any())
<div class="pv-alert error"><i class="fa-solid fa-triangle-exclamation"></i><div><strong>Please check the form</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
@endif

<div class="pv-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card-top"><div><span class="admin-stat-label">Order Total</span><strong class="admin-stat-value">{{ strtoupper((string)($order->currency ?: 'USD')) }} {{ number_format((float)$order->total, 2) }}</strong></div><span class="admin-stat-icon"><i class="fa-solid fa-dollar-sign"></i></span></div>
        <span class="pv-note">Order #{{ $order->order_number ?: $order->id }}</span>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top"><div><span class="admin-stat-label">Payment Provider</span><strong class="admin-stat-value small">{{ $providerLabel }}</strong></div><span class="admin-stat-icon"><i class="{{ $providerIcon }}"></i></span></div>
        <span class="pv-note">{{ $isBank ? 'Manual verification' : 'Provider verification' }}</span>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top"><div><span class="admin-stat-label">Payment Status</span><strong class="admin-stat-value small">{{ ucwords(str_replace('_',' ',$status)) }}</strong></div><span class="admin-stat-icon"><i class="fa-solid fa-credit-card"></i></span></div>
        <span class="pv-note">Current payment state</span>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top"><div><span class="admin-stat-label">Verification</span><strong class="admin-stat-value small">{{ $verificationLabel }}</strong></div><span class="admin-stat-icon"><i class="{{ $verified ? 'fa-solid fa-circle-check' : ($rejected ? 'fa-solid fa-circle-xmark' : 'fa-regular fa-clock') }}"></i></span></div>
        <span class="pv-note">Verification result</span>
    </div>
</div>

<div class="pv-layout">
    <main class="pv-main">
        <section class="admin-panel pv-panel">
            <div class="admin-panel-header">
                <div><span class="admin-panel-eyebrow">PAYMENT DETAILS</span><h3>Transaction Information</h3><p>Provider and transaction identifiers attached to this order.</p></div>
                <span class="pv-panel-icon"><i class="{{ $providerIcon }}"></i></span>
            </div>

            <div class="pv-info-grid">
                <div><span>Provider</span><strong>{{ $providerLabel }}</strong></div>
                <div><span>Payment Status</span><strong><em class="pv-badge status-{{ $status }}"><i></i>{{ ucwords(str_replace('_',' ',$status)) }}</em></strong></div>
                <div><span>Payment Reference</span><strong class="break">{{ $reference ?: 'Not provided' }}</strong></div>
                <div><span>Transaction / Payment ID</span><strong class="break">{{ $transactionId ?: 'Not available' }}</strong></div>
                <div><span>Amount</span><strong>{{ strtoupper((string)($order->currency ?: 'USD')) }} {{ number_format((float)$order->total, 2) }}</strong></div>
                <div><span>Paid At</span><strong>{{ $order->paid_at?->format('d M Y, h:i A') ?: 'Not paid yet' }}</strong></div>
            </div>
        </section>

        <section class="admin-panel pv-panel">
            <div class="admin-panel-header">
                <div><span class="admin-panel-eyebrow">ORDER CONTEXT</span><h3>Order & Customer</h3><p>Information used to match this payment with its order.</p></div>
                <span class="pv-panel-icon"><i class="fa-solid fa-user"></i></span>
            </div>

            <div class="pv-info-grid">
                <div><span>Order ID</span><strong>{{ $order->order_number ?: '#' . $order->id }}</strong></div>
                <div><span>Order Status</span><strong>{{ ucwords(str_replace('_',' ',(string)($order->order_status ?: 'pending'))) }}</strong></div>
                <div><span>Customer</span><strong>{{ $customerName }}</strong></div>
                <div><span>Email</span><strong class="break">{{ $customerEmail }}</strong></div>
                <div><span>Order Date</span><strong>{{ $order->created_at?->format('d M Y, h:i A') ?: '—' }}</strong></div>
                <div><span>Customer Account</span><strong>{{ $order->user_id ? 'Registered customer' : 'Guest' }}</strong></div>
            </div>
        </section>

        <section class="admin-panel pv-panel">
            <div class="admin-panel-header">
                <div><span class="admin-panel-eyebrow">PURCHASE</span><h3>Order Items</h3><p>Products attached to this payment record.</p></div>
                <span class="pv-count">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item',$order->items->count()) }}</span>
            </div>

            @if($order->items->count())
            <div class="admin-table-wrapper">
                <table class="admin-table pv-table">
                    <thead><tr><th>Item</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th class="right">Total</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        @php
                            $name = $item->product_name ?? $item->name ?? $item->title ?? 'Order item';
                            $sku = $item->sku ?? $item->product_sku ?? '—';
                            $qty = (int)($item->quantity ?? $item->qty ?? 1);
                            $unit = (float)($item->unit_price ?? $item->price ?? 0);
                            $line = (float)($item->total ?? $item->line_total ?? ($unit * $qty));
                        @endphp
                        <tr>
                            <td><span class="pv-product"><i class="fa-solid fa-box"></i><strong>{{ $name }}</strong></span></td>
                            <td>{{ $sku }}</td><td>{{ $qty }}</td>
                            <td>{{ strtoupper((string)($order->currency ?: 'USD')) }} {{ number_format($unit,2) }}</td>
                            <td class="right"><strong>{{ strtoupper((string)($order->currency ?: 'USD')) }} {{ number_format($line,2) }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pv-empty"><i class="fa-solid fa-box-open"></i><p>No order items are available.</p></div>
            @endif
        </section>
    </main>

    <aside class="pv-side">
        <section class="admin-panel pv-panel">
            <div class="admin-panel-header"><div><span class="admin-panel-eyebrow">VERIFICATION</span><h3>Payment Review</h3></div><span class="pv-panel-icon"><i class="fa-solid fa-shield-halved"></i></span></div>
            <div class="pv-review">
                <div class="pv-state {{ $verificationClass }}">
                    <i class="{{ $verified ? 'fa-solid fa-circle-check' : ($rejected ? 'fa-solid fa-circle-xmark' : 'fa-regular fa-clock') }}"></i>
                    <div><strong>{{ $verificationLabel }}</strong><p>@if($verified) This payment has been verified. @elseif($rejected) This bank-transfer payment was rejected. @elseif($isBank) Review the bank payment before confirming it. @else Payment state is controlled by the provider. @endif</p></div>
                </div>

                @if($isBank && !$verified && !$rejected)
                <form method="POST" action="{{ route('admin.payment-verifications.verify-bank-transfer',$order) }}" class="pv-form">
                    @csrf
                    <label>Payment Reference <b>*</b><input type="text" name="payment_reference" value="{{ old('payment_reference',$reference) }}" maxlength="255" required></label>
                    <label>Verification Notes<textarea name="admin_notes" rows="4" maxlength="5000" placeholder="Optional internal verification notes...">{{ old('admin_notes',$order->admin_notes) }}</textarea></label>
                    <button type="submit" class="admin-button admin-button-primary pv-full" onclick="return confirm('Verify this bank transfer and mark the payment as paid?');"><i class="fa-solid fa-circle-check"></i> Verify Payment</button>
                </form>

                <div class="pv-or"><span>OR</span></div>

                <form method="POST" action="{{ route('admin.payment-verifications.reject-bank-transfer',$order) }}" class="pv-form">
                    @csrf
                    <label>Rejection Reason <b>*</b><textarea name="admin_notes" rows="4" maxlength="5000" required placeholder="Explain why this payment cannot be verified...">{{ old('admin_notes') }}</textarea></label>
                    <button type="submit" class="pv-reject" onclick="return confirm('Reject this bank-transfer payment?');"><i class="fa-solid fa-circle-xmark"></i> Reject Payment</button>
                </form>
                @elseif($isBank)
                    <div class="pv-meta"><span>Payment Reference</span><strong class="break">{{ $reference ?: 'Not provided' }}</strong></div>
                    @if($order->admin_notes)<div class="pv-notes"><span>Admin Notes</span><p>{{ $order->admin_notes }}</p></div>@endif
                @else
                    <div class="pv-provider-note"><i class="fa-solid fa-lock"></i><div><strong>Provider-controlled payment</strong><p>Stripe payment status is shown for review and cannot be manually marked paid here.</p></div></div>
                @endif
            </div>
        </section>

        <section class="admin-panel pv-panel">
            <div class="admin-panel-header"><div><span class="admin-panel-eyebrow">QUICK DETAILS</span><h3>Record Information</h3></div></div>
            <div class="pv-list">
                <div><span>Database ID</span><strong>#{{ $order->id }}</strong></div>
                <div><span>Currency</span><strong>{{ strtoupper((string)($order->currency ?: 'USD')) }}</strong></div>
                <div><span>Payment Method</span><strong>{{ ucwords(str_replace('_',' ',(string)($order->payment_method ?: $provider))) }}</strong></div>
                <div><span>Created</span><strong>{{ $order->created_at?->format('d M Y') ?: '—' }}</strong></div>
                <div><span>Last Updated</span><strong>{{ $order->updated_at?->format('d M Y, h:i A') ?: '—' }}</strong></div>
            </div>
        </section>
    </aside>
</div>

<style>
.pv-header{margin-bottom:18px}.pv-header .admin-page-eyebrow,.pv-panel .admin-panel-eyebrow{color:#635bff;font-size:9px;font-weight:800;letter-spacing:.12em}.pv-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}.pv-header p{margin:0;color:#7b8497;font-size:12px}.pv-alert{display:flex;gap:10px;margin-bottom:18px;padding:13px 15px;border:1px solid;border-radius:10px;font-size:11px}.pv-alert strong{display:block}.pv-alert p{margin:2px 0}.pv-alert.success{border-color:#b9e6cc;background:#f1fbf5;color:#167149}.pv-alert.error{border-color:#f1c7c7;background:#fff5f5;color:#ad3030}
.pv-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.pv-stats .admin-stat-card{min-height:116px;padding:17px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.02)}.pv-stats .admin-stat-label{color:#687386;font-size:10px}.pv-stats .admin-stat-value{display:block;margin-top:13px;color:#111827;font-size:19px;font-weight:800}.pv-stats .admin-stat-value.small{font-size:16px}.pv-stats .admin-stat-icon{width:34px;height:34px;border:0;border-radius:9px;background:#eef0ff;color:#635bff;font-size:13px}.pv-stats .admin-stat-card:nth-child(2) .admin-stat-icon{background:#edf4ff;color:#3974dc}.pv-stats .admin-stat-card:nth-child(3) .admin-stat-icon{background:#fff4df;color:#d88716}.pv-stats .admin-stat-card:nth-child(4) .admin-stat-icon{background:#eaf8f1;color:#13875b}.pv-note{display:block;margin-top:10px;color:#8a93a4;font-size:9px}
.pv-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:18px;align-items:start}.pv-main,.pv-side{display:flex;flex-direction:column;gap:18px}.pv-panel{overflow:hidden;margin:0;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:none}.pv-panel .admin-panel-header{padding:16px 18px;border-bottom:1px solid #edf0f5}.pv-panel .admin-panel-header h3{margin:3px 0 0;color:#172033;font-size:14px;font-weight:800}.pv-panel .admin-panel-header p{margin:4px 0 0;color:#8a93a4;font-size:10px}.pv-panel-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;background:#eef0ff;color:#635bff;font-size:12px}.pv-count{padding:6px 9px;border-radius:999px;background:#f2f4f7;color:#596274;font-size:9px;font-weight:800}
.pv-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.pv-info-grid>div{min-width:0;padding:16px 18px;border-right:1px solid #edf0f5;border-bottom:1px solid #edf0f5}.pv-info-grid>div:nth-child(2n){border-right:0}.pv-info-grid>div:nth-last-child(-n+2){border-bottom:0}.pv-info-grid span,.pv-meta span,.pv-notes span{display:block;margin-bottom:6px;color:#8a93a4;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.pv-info-grid strong,.pv-meta strong{display:block;color:#263044;font-size:11px}.break{overflow-wrap:anywhere;word-break:break-word}.pv-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border-radius:999px;font-size:8px;font-style:normal;font-weight:800}.pv-badge i{width:6px;height:6px;border-radius:50%;background:currentColor}.status-pending,.status-partially_paid{background:#fff4dc;color:#a86a00}.status-paid,.status-completed,.status-succeeded{background:#e8f8ef;color:#148457}.status-failed,.status-declined,.status-cancelled{background:#ffeded;color:#c13b3b}.status-refunded{background:#f1f2f4;color:#62666d}
.pv-table thead{background:#fbfcfe}.pv-table th{padding:10px 13px;color:#7c8596;font-size:8px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.pv-table td{padding:11px 13px;color:#465064;font-size:9px}.pv-product{display:flex;align-items:center;gap:8px}.pv-product>i{display:inline-flex;align-items:center;justify-content:center;width:29px;height:29px;border-radius:7px;background:#eef0ff;color:#635bff;font-size:9px}.pv-product strong{color:#253047;font-size:10px}.right{text-align:right!important}.pv-empty{padding:35px;text-align:center;color:#8a93a4;font-size:10px}.pv-empty i{color:#635bff;font-size:18px}
.pv-review{padding:16px}.pv-state{display:flex;gap:10px;margin-bottom:16px;padding:12px;border-radius:9px}.pv-state>i{margin-top:2px}.pv-state strong{display:block;font-size:11px}.pv-state p{margin:3px 0 0;font-size:9px;line-height:1.55}.pv-state.verified{background:#eaf8f1;color:#16734c}.pv-state.pending{background:#fff6e4;color:#9d6900}.pv-state.rejected{background:#fff0f0;color:#b33333}.pv-form{display:flex;flex-direction:column;gap:13px}.pv-form label{color:#465064;font-size:9px;font-weight:800}.pv-form label b{color:#d03c3c}.pv-form input,.pv-form textarea{display:block;width:100%;margin-top:6px;border:1px solid #dfe4eb;border-radius:7px;outline:0;background:#fff;color:#344054;font:inherit;font-size:10px}.pv-form input{min-height:39px;padding:9px 11px}.pv-form textarea{padding:10px 11px;resize:vertical}.pv-form input:focus,.pv-form textarea:focus{border-color:#8b85ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.pv-full{width:100%;justify-content:center;border-color:#635bff!important;background:#635bff!important}.pv-or{display:flex;align-items:center;gap:8px;margin:16px 0;color:#a0a6b2;font-size:8px;font-weight:800}.pv-or:before,.pv-or:after{content:"";flex:1;height:1px;background:#e8ebf0}.pv-reject{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;min-height:38px;border:1px solid #efc5c5;border-radius:7px;background:#fff;color:#b63838;font-size:9px;font-weight:800;cursor:pointer}.pv-reject:hover{background:#c23c3c;color:#fff}.pv-meta{padding:11px 0;border-bottom:1px solid #edf0f5}.pv-notes{margin-top:14px;padding:12px;border-radius:8px;background:#f8f9fb}.pv-notes p{margin:0;color:#566074;font-size:9px;line-height:1.55;white-space:pre-wrap}.pv-provider-note{display:flex;gap:10px;padding:12px;border-radius:9px;background:#eef4ff;color:#315fba}.pv-provider-note strong{font-size:10px}.pv-provider-note p{margin:3px 0 0;font-size:9px;line-height:1.55}
.pv-list{padding:5px 16px}.pv-list>div{display:flex;justify-content:space-between;gap:15px;padding:11px 0;border-bottom:1px solid #edf0f5}.pv-list>div:last-child{border-bottom:0}.pv-list span{color:#8a93a4;font-size:9px}.pv-list strong{max-width:58%;color:#30394a;font-size:9px;text-align:right;overflow-wrap:anywhere}
@media(max-width:1180px){.pv-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.pv-layout{grid-template-columns:minmax(0,1fr) 300px}}@media(max-width:900px){.pv-layout{grid-template-columns:1fr}.pv-side{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:680px){.pv-stats,.pv-info-grid{grid-template-columns:1fr}.pv-info-grid>div,.pv-info-grid>div:nth-child(2n),.pv-info-grid>div:nth-last-child(-n+2){border-right:0;border-bottom:1px solid #edf0f5}.pv-info-grid>div:last-child{border-bottom:0}.pv-side{display:flex}}
</style>
@endsection
