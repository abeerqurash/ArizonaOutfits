<?php $__env->startSection('title', 'Order Payment Verification'); ?>

<?php $__env->startSection('page-heading', 'Order Payment Verification'); ?>



<?php $__env->startSection('content'); ?>



<?php

    $records = collect($orders->items());



    $pendingReviewCount = $records->filter(function ($order) {

        $provider = $order->payment_provider ?: $order->payment_method;



        if ($provider !== 'bank_transfer') {

            return false;

        }



        $metadata = $order->payment_metadata;



        if (is_string($metadata)) {

            $decoded = json_decode($metadata, true);

            $metadata = is_array($decoded) ? $decoded : [];

        }



        $metadata = is_array($metadata) ? $metadata : [];

        $verification = $metadata['bank_transfer_verification'] ?? [];



        return empty($verification['verified'])

            && empty($verification['rejected_at'])

            && strtolower((string) $order->payment_status) !== 'paid';

    })->count();



    $paidCount = $records->filter(

        fn ($order) => in_array(

            strtolower((string) $order->payment_status),

            ['paid', 'completed', 'succeeded'],

            true

        )

    )->count();



    $bankTransferCount = $records->filter(

        fn ($order) =>

            ($order->payment_provider ?: $order->payment_method)

            === 'bank_transfer'

    )->count();



    $stripeCount = $records->filter(

        fn ($order) =>

            ($order->payment_provider ?: $order->payment_method)

            === 'stripe'

    )->count();

?>



<div class="admin-page-header payment-dashboard-header">

    <div>

        <span class="admin-page-eyebrow">

            PAYMENT OPERATIONS

        </span>



        <h2>Order Payment Verification</h2>



        <p>

            Review bank transfers, payment references and provider-verified

            transactions from one place.

        </p>

    </div>



    <div class="admin-page-actions">

        <a

            href="<?php echo e(route('admin.orders.index')); ?>"

            class="admin-button admin-button-secondary"

        >

            <i class="fa-solid fa-bag-shopping"></i>

            View Orders

        </a>

    </div>

</div>









<div class="payment-statistics-grid">



    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div>

                <span class="admin-stat-label">

                    Total Records

                </span>



                <strong class="admin-stat-value">

                    <?php echo e(number_format($orders->total())); ?>


                </strong>

            </div>



            <span class="admin-stat-icon">

                <i class="fa-solid fa-receipt"></i>

            </span>

        </div>



        <span class="payment-stat-note">

            Matching current filters

        </span>

    </div>





    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div>

                <span class="admin-stat-label">

                    Pending Review

                </span>



                <strong class="admin-stat-value">

                    <?php echo e(number_format($pendingReviewCount)); ?>


                </strong>

            </div>



            <span class="admin-stat-icon">

                <i class="fa-solid fa-clock"></i>

            </span>

        </div>



        <span class="payment-stat-note">

            On this page

        </span>

    </div>





    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div>

                <span class="admin-stat-label">

                    Paid

                </span>



                <strong class="admin-stat-value">

                    <?php echo e(number_format($paidCount)); ?>


                </strong>

            </div>



            <span class="admin-stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </span>

        </div>



        <span class="payment-stat-note">

            On this page

        </span>

    </div>





    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div>

                <span class="admin-stat-label">

                    Payment Sources

                </span>



                <strong class="admin-stat-value payment-source-value">

                    <?php echo e($bankTransferCount); ?> / <?php echo e($stripeCount); ?>


                </strong>

            </div>



            <span class="admin-stat-icon">

                <i class="fa-solid fa-credit-card"></i>

            </span>

        </div>



        <span class="payment-stat-note">

            Bank / Stripe on this page

        </span>

    </div>



</div>









<section class="admin-panel payment-filter-panel">



    <div class="admin-panel-header">

        <div>

            <span class="admin-panel-eyebrow">

                Find payments

            </span>



            <h3>Search & Filter</h3>

        </div>



        <div class="payment-panel-icon">

            <i class="fa-solid fa-sliders"></i>

        </div>

    </div>





    <form

        method="GET"

        action="<?php echo e(route('admin.payment-verifications.index')); ?>"

        class="payment-filter-form"

    >



        <div class="payment-search-field">

            <i class="fa-solid fa-magnifying-glass"></i>



            <input

                type="search"

                name="search"

                value="<?php echo e(request('search')); ?>"

                placeholder="Search order, payment reference, customer or email..."

                aria-label="Search payment verification records"

            >

        </div>





        <div class="payment-select-field">

            <i class="fa-solid fa-building-columns"></i>



            <select

                name="provider"

                aria-label="Filter by payment provider"

            >

                <option value="">All Providers</option>



                <option

                    value="bank_transfer"

                    <?php if(request('provider') === 'bank_transfer'): echo 'selected'; endif; ?>

                >

                    Bank Transfer

                </option>



                <option

                    value="stripe"

                    <?php if(request('provider') === 'stripe'): echo 'selected'; endif; ?>

                >

                    Stripe

                </option>

            </select>



            <i class="fa-solid fa-chevron-down payment-select-arrow"></i>

        </div>





        <div class="payment-select-field">

            <i class="fa-solid fa-circle-dollar-to-slot"></i>



            <select

                name="payment_status"

                aria-label="Filter by payment status"

            >

                <option value="">All Payment Statuses</option>



                <?php $__currentLoopData = [

                    'pending' => 'Pending',

                    'paid' => 'Paid',

                    'partially_paid' => 'Partially Paid',

                    'completed' => 'Completed',

                    'succeeded' => 'Succeeded',

                    'failed' => 'Failed',

                    'declined' => 'Declined',

                    'cancelled' => 'Cancelled',

                    'refunded' => 'Refunded',

                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>



                    <option

                        value="<?php echo e($value); ?>"

                        <?php if(request('payment_status') === $value): echo 'selected'; endif; ?>

                    >

                        <?php echo e($label); ?>


                    </option>



                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </select>



            <i class="fa-solid fa-chevron-down payment-select-arrow"></i>

        </div>





        <div class="payment-filter-actions">



            <button

                type="submit"

                class="admin-button admin-button-primary"

            >

                <i class="fa-solid fa-filter"></i>

                Apply Filters

            </button>



            <?php if(

                request()->filled('search')

                || request()->filled('provider')

                || request()->filled('payment_status')

            ): ?>

                <a

                    href="<?php echo e(route('admin.payment-verifications.index')); ?>"

                    class="admin-button admin-button-secondary"

                >

                    <i class="fa-solid fa-rotate-left"></i>

                    Reset

                </a>

            <?php endif; ?>



        </div>



    </form>



</section>









<?php if(session('success') || session('error')): ?>
    <?php
        $paymentPopupIsError = session('error') !== null;
        $paymentPopupMessage = session('error') ?: session('success');
    ?>
    <div class="pv-popup" id="paymentFeedbackPopup" role="dialog" aria-modal="true">
        <button type="button" class="pv-popup-backdrop" data-pv-popup-close aria-label="Close"></button>
        <div class="pv-popup-card">
            <button type="button" class="pv-popup-close" data-pv-popup-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            <span class="pv-popup-icon <?php echo e($paymentPopupIsError ? 'error' : 'success'); ?>"><i class="fa-solid <?php echo e($paymentPopupIsError ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"></i></span>
            <span class="admin-page-eyebrow">PAYMENT VERIFICATION</span>
            <h3><?php echo e($paymentPopupIsError ? 'Action required' : 'Success'); ?></h3>
            <p><?php echo e($paymentPopupMessage); ?></p>
            <button type="button" class="admin-button admin-button-primary" data-pv-popup-close>Continue</button>
        </div>
    </div>
<?php endif; ?>









<section class="admin-panel payment-records-panel">



    <div class="admin-panel-header">

        <div>

            <span class="admin-panel-eyebrow">

                Verification queue

            </span>



            <h3>Payment Records</h3>



            <p class="payment-panel-description">

                Review payment information before updating bank-transfer

                orders.

            </p>

        </div>



        <div class="payment-record-count">

            <i class="fa-solid fa-list-check"></i>



            <span>

                <?php echo e(number_format($orders->total())); ?>


                <?php echo e(\Illuminate\Support\Str::plural('record', $orders->total())); ?>


            </span>

        </div>

    </div>





    <?php if($orders->count()): ?>



        <div class="admin-table-wrapper payment-table-wrapper">



            <table class="admin-table payment-table">



                <thead>

                    <tr>

                        <th>Order</th>

                        <th>Customer</th>

                        <th>Provider</th>

                        <th>Reference</th>

                        <th>Amount</th>

                        <th>Payment</th>

                        <th>Verification</th>

                        <th>Date</th>

                        <th class="payment-action-heading">

                            Action

                        </th>

                    </tr>

                </thead>





                <tbody>



                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>



                        <?php

                            $provider = (string) (

                                $order->payment_provider

                                ?: $order->payment_method

                            );



                            $status = strtolower(

                                (string) ($order->payment_status ?: 'pending')

                            );



                            $metadata = $order->payment_metadata;



                            if (is_string($metadata)) {

                                $decodedMetadata = json_decode(

                                    $metadata,

                                    true

                                );



                                $metadata = is_array($decodedMetadata)

                                    ? $decodedMetadata

                                    : [];

                            }



                            $metadata = is_array($metadata)

                                ? $metadata

                                : [];



                            $bankVerification =

                                $metadata['bank_transfer_verification']

                                ?? [];



                            /*

                             * Verification wording depends on the provider.

                             *

                             * Bank transfer is manually reviewed by an admin.

                             * Stripe is provider-controlled and must only be

                             * described as verified after its payment status

                             * has actually reached a successful paid state.

                             */

                            if ($provider === 'bank_transfer') {

                                if (!empty($bankVerification['verified'])) {

                                    $verificationLabel = 'Verified';

                                    $verificationClass = 'verified';

                                } elseif (!empty($bankVerification['rejected_at'])) {

                                    $verificationLabel = 'Rejected';

                                    $verificationClass = 'rejected';

                                } else {

                                    $verificationLabel = 'Pending Review';

                                    $verificationClass = 'pending';

                                }

                            } elseif ($provider === 'stripe') {

                                if (in_array(

                                    $status,

                                    ['paid', 'completed', 'succeeded'],

                                    true

                                )) {

                                    $verificationLabel = 'Provider Verified';

                                    $verificationClass = 'verified';

                                } elseif (in_array(

                                    $status,

                                    ['failed', 'declined', 'cancelled'],

                                    true

                                )) {

                                    $verificationLabel = 'Provider Failed';

                                    $verificationClass = 'rejected';

                                } else {

                                    $verificationLabel = 'Awaiting Stripe';

                                    $verificationClass = 'pending';

                                }

                            } else {

                                $verificationLabel = 'Provider Controlled';

                                $verificationClass = 'pending';

                            }



                            $customerName =

                                $order->billing_name

                                ?: $order->shipping_name

                                ?: $order->user?->name

                                ?: 'Customer';



                            $customerEmail =

                                $order->billing_email

                                ?: $order->shipping_email

                                ?: $order->user?->email

                                ?: '';



                            $providerIcon = match ($provider) {

                                'stripe' => 'fa-brands fa-stripe-s',

                                'bank_transfer' => 'fa-solid fa-building-columns',

                                default => 'fa-solid fa-wallet',

                            };

                        ?>





                        <tr>



                            <td data-label="Order">

                                <div class="payment-order-cell">

                                    <span class="payment-order-icon">

                                        <i class="fa-solid fa-bag-shopping"></i>

                                    </span>



                                    <div>

                                        <a

                                            href="<?php echo e(route('admin.payment-verifications.show', $order)); ?>"

                                            class="payment-order-number"

                                        >

                                            <?php echo e($order->order_number ?: '#' . $order->id); ?>


                                        </a>



                                        <small>

                                            ID #<?php echo e($order->id); ?>


                                        </small>

                                    </div>

                                </div>

                            </td>





                            <td data-label="Customer">

                                <div class="payment-customer-cell">

                                    <span class="payment-customer-avatar">

                                        <?php echo e(strtoupper(

                                            mb_substr(

                                                trim((string) $customerName),

                                                0,

                                                1

                                            )

                                        )); ?>


                                    </span>



                                    <div>

                                        <strong>

                                            <?php echo e($customerName); ?>


                                        </strong>



                                        <?php if($customerEmail): ?>

                                            <small>

                                                <?php echo e($customerEmail); ?>


                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>





                            <td data-label="Provider">

                                <span class="payment-provider">

                                    <i class="<?php echo e($providerIcon); ?>"></i>



                                    <?php echo e(ucwords(

                                        str_replace(

                                            '_',

                                            ' ',

                                            $provider ?: 'Unknown'

                                        )

                                    )); ?>


                                </span>

                            </td>





                            <td data-label="Reference">

                                <?php if($order->payment_reference): ?>

                                    <code class="payment-reference">

                                        <?php echo e($order->payment_reference); ?>


                                    </code>

                                <?php else: ?>

                                    <span class="payment-muted">

                                        Not provided

                                    </span>

                                <?php endif; ?>

                            </td>





                            <td data-label="Amount">

                                <strong class="payment-amount">

                                    <?php echo e(strtoupper(

                                        (string) ($order->currency ?: 'USD')

                                    )); ?>




                                    <?php echo e(number_format(

                                        (float) $order->total,

                                        2

                                    )); ?>


                                </strong>

                            </td>





                            <td data-label="Payment">

                                <span

                                    class="payment-status-badge payment-status-<?php echo e($status); ?>"

                                >

                                    <span class="payment-status-dot"></span>



                                    <?php echo e(ucwords(

                                        str_replace('_', ' ', $status)

                                    )); ?>


                                </span>

                            </td>





                            <td data-label="Verification">

                                <span

                                    class="verification-badge verification-<?php echo e($verificationClass); ?>"

                                >

                                    <?php if($verificationClass === 'verified'): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php elseif($verificationClass === 'rejected'): ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php else: ?>

                                        <i class="fa-regular fa-clock"></i>

                                    <?php endif; ?>



                                    <?php echo e($verificationLabel); ?>


                                </span>

                            </td>





                            <td data-label="Date">

                                <div class="payment-date-cell">

                                    <strong>

                                        <?php echo e($order->created_at?->format('d M Y') ?: '—'); ?>


                                    </strong>



                                    <small>

                                        <?php echo e($order->created_at?->format('h:i A')); ?>


                                    </small>

                                </div>

                            </td>





                            <td

                                data-label="Action"

                                class="payment-action-cell"

                            >

                                <a

                                    href="<?php echo e(route('admin.payment-verifications.show', $order)); ?>"

                                    class="payment-review-button"

                                    title="Review payment"

                                    aria-label="Review payment for order <?php echo e($order->order_number ?: $order->id); ?>"

                                >

                                    <span>Review</span>

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>

                            </td>



                        </tr>



                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



                </tbody>



            </table>



        </div>





        <?php if($orders->hasPages()): ?>



            <div class="payment-pagination">

                <div class="payment-pagination-summary">

                    Showing

                    <strong><?php echo e($orders->firstItem()); ?></strong>

                    to

                    <strong><?php echo e($orders->lastItem()); ?></strong>

                    of

                    <strong><?php echo e($orders->total()); ?></strong>

                    records

                </div>



                <div>

                    <?php if($orders->hasPages()): ?>
                    <nav class="pv-pagination" aria-label="Payment verification pagination">
                        <?php if($orders->onFirstPage()): ?>
                            <span class="pv-page-button is-disabled" aria-disabled="true">
                                <i class="fa-solid fa-chevron-left"></i>
                                <span>Previous</span>
                            </span>
                        <?php else: ?>
                            <a class="pv-page-button" href="<?php echo e($orders->previousPageUrl()); ?>" rel="prev">
                                <i class="fa-solid fa-chevron-left"></i>
                                <span>Previous</span>
                            </a>
                        <?php endif; ?>

                        <div class="pv-page-numbers">
                            <?php $__currentLoopData = $orders->getUrlRange(1, $orders->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $orders->currentPage()): ?>
                                    <span class="pv-page-number is-active" aria-current="page"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <a class="pv-page-number" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($orders->hasMorePages()): ?>
                            <a class="pv-page-button" href="<?php echo e($orders->nextPageUrl()); ?>" rel="next">
                                <span>Next</span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="pv-page-button is-disabled" aria-disabled="true">
                                <span>Next</span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                </div>

            </div>



        <?php endif; ?>



    <?php else: ?>



        <div class="payment-empty-state">



            <div class="payment-empty-icon">

                <i class="fa-solid fa-receipt"></i>

            </div>



            <h3>No payment records found</h3>



            <p>

                No payment records match the filters you selected.

            </p>



            <?php if(

                request()->filled('search')

                || request()->filled('provider')

                || request()->filled('payment_status')

            ): ?>

                <a

                    href="<?php echo e(route('admin.payment-verifications.index')); ?>"

                    class="admin-button admin-button-secondary"

                >

                    <i class="fa-solid fa-rotate-left"></i>

                    Clear Filters

                </a>

            <?php endif; ?>



        </div>



    <?php endif; ?>



</section>





<style>

.payment-statistics-grid {

    display: grid;

    grid-template-columns: repeat(4, minmax(0, 1fr));

    gap: 18px;

    margin-bottom: 24px;

}



.payment-stat-note {

    display: block;

    margin-top: 10px;

    color: #8a8a8a;

    font-size: 12px;

}



.payment-source-value {

    font-size: 26px;

}



.payment-filter-panel,

.payment-records-panel {

    margin-bottom: 24px;

}



.payment-panel-icon,

.payment-record-count {

    display: inline-flex;

    align-items: center;

    justify-content: center;

}



.payment-panel-icon {

    width: 42px;

    height: 42px;

    border: 1px solid #e7e7e7;

    border-radius: 12px;

    background: #f8f8f8;

    color: #202020;

}



.payment-filter-form {

    display: grid;

    grid-template-columns:

        minmax(240px, 1.6fr)

        minmax(180px, .8fr)

        minmax(190px, .8fr)

        auto;

    gap: 12px;

    align-items: center;

    padding: 20px;

}



.payment-search-field,

.payment-select-field {

    position: relative;

}



.payment-search-field > i,

.payment-select-field > i:first-child {

    position: absolute;

    top: 50%;

    left: 15px;

    z-index: 1;

    transform: translateY(-50%);

    color: #8b8b8b;

    pointer-events: none;

}



.payment-search-field input,

.payment-select-field select {

    width: 100%;

    min-height: 46px;

    border: 1px solid #dedede;

    border-radius: 10px;

    outline: 0;

    background: #fff;

    color: #202020;

    font: inherit;

    transition:

        border-color .2s ease,

        box-shadow .2s ease;

}



.payment-search-field input {

    padding: 10px 14px 10px 43px;

}



.payment-select-field select {

    appearance: none;

    padding: 10px 38px 10px 43px;

    cursor: pointer;

}



.payment-search-field input:focus,

.payment-select-field select:focus {

    border-color: #9d9d9d;

    box-shadow: 0 0 0 3px rgba(0, 0, 0, .04);

}



.payment-select-arrow {

    position: absolute;

    top: 50%;

    right: 14px;

    transform: translateY(-50%);

    color: #888;

    font-size: 11px;

    pointer-events: none;

}



.payment-filter-actions {

    display: flex;

    gap: 9px;

}



.payment-alert {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    margin-bottom: 24px;

    padding: 15px 17px;

    border: 1px solid;

    border-radius: 12px;

}



.payment-alert-icon {

    display: inline-flex;

    margin-top: 2px;

    font-size: 17px;

}



.payment-alert strong {

    display: block;

    margin-bottom: 3px;

}



.payment-alert p {

    margin: 0;

    font-size: 13px;

}



.payment-alert-success {

    border-color: #b7e4cc;

    background: #f2fbf6;

    color: #176b45;

}



.payment-alert-error {

    border-color: #f0c0c0;

    background: #fff5f5;

    color: #a22a2a;

}



.payment-panel-description {

    margin: 5px 0 0;

    color: #888;

    font-size: 13px;

}



.payment-record-count {

    gap: 8px;

    padding: 8px 12px;

    border: 1px solid #e7e7e7;

    border-radius: 999px;

    background: #fafafa;

    color: #555;

    font-size: 12px;

    font-weight: 700;

}



.payment-table-wrapper {

    border-top: 1px solid #ededed;

}



.payment-table th,

.payment-table td {

    vertical-align: middle;

}



.payment-order-cell,

.payment-customer-cell {

    display: flex;

    align-items: center;

    gap: 11px;

    min-width: 175px;

}



.payment-order-icon,

.payment-customer-avatar {

    display: inline-flex;

    flex: 0 0 38px;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

    border: 1px solid #e7e7e7;

    border-radius: 10px;

    background: #f8f8f8;

}



.payment-customer-avatar {

    border-radius: 50%;

    background: #1f1f1f;

    color: #fff;

    font-size: 12px;

    font-weight: 800;

}



.payment-order-number {

    display: inline-block;

    color: #1f1f1f;

    font-weight: 800;

    text-decoration: none;

}



.payment-order-number:hover {

    text-decoration: underline;

}



.payment-order-cell small,

.payment-customer-cell small,

.payment-date-cell small {

    display: block;

    margin-top: 3px;

    color: #8b8b8b;

    font-size: 11px;

}



.payment-customer-cell strong,

.payment-date-cell strong {

    display: block;

    color: #262626;

    font-size: 13px;

}



.payment-provider {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    min-width: max-content;

    font-size: 13px;

    font-weight: 700;

}



.payment-provider i {

    width: 16px;

    text-align: center;

}



.payment-reference {

    display: inline-block;

    max-width: 180px;

    overflow: hidden;

    padding: 5px 8px;

    border-radius: 6px;

    background: #f5f5f5;

    color: #444;

    font-family: inherit;

    font-size: 11px;

    text-overflow: ellipsis;

    white-space: nowrap;

}



.payment-muted {

    color: #aaa;

    font-size: 12px;

}



.payment-amount {

    white-space: nowrap;

    font-size: 13px;

}



.payment-status-badge,

.verification-badge {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 9px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 800;

    white-space: nowrap;

}



.payment-status-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: currentColor;

}



.payment-status-pending,

.payment-status-partially_paid,

.verification-pending {

    background: #fff8e8;

    color: #9a6800;

}



.payment-status-paid,

.payment-status-completed,

.payment-status-succeeded,

.verification-verified {

    background: #edf9f2;

    color: #18734b;

}



.payment-status-failed,

.payment-status-declined,

.payment-status-cancelled,

.verification-rejected {

    background: #fff0f0;

    color: #b22f2f;

}



.payment-status-refunded {

    background: #f1f2f4;

    color: #62666d;

}



.payment-action-heading,

.payment-action-cell {

    text-align: right !important;

}



.payment-review-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 35px;

    padding: 7px 11px;

    border: 1px solid #dedede;

    border-radius: 8px;

    background: #fff;

    color: #222;

    font-size: 11px;

    font-weight: 800;

    text-decoration: none;

    transition:

        *background* .2s ease,

        border-color .2s ease,

        color .2s ease;

}



.payment-review-button:hover {

    border-color: #202020;

    background: #202020;

    color: #fff;

}



.payment-pagination {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 18px 20px;

    border-top: 1px solid #ededed;

}



.payment-pagination-summary {

    color: #777;

    font-size: 12px;

}



.payment-empty-state {

    display: flex;

    align-items: center;

    flex-direction: column;

    padding: 65px 20px;

    text-align: center;

}



.payment-empty-icon {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 62px;

    height: 62px;

    margin-bottom: 17px;

    border: 1px solid #e5e5e5;

    border-radius: 18px;

    background: #f8f8f8;

    color: #555;

    font-size: 22px;

}



.payment-empty-state h3 {

    margin: 0 0 7px;

    color: #242424;

}



.payment-empty-state p {

    max-width: 420px;

    margin: 0 0 18px;

    color: #888;

    font-size: 13px;

}



@media (max-width: 1250px) {

    .payment-statistics-grid {

        grid-template-columns: repeat(2, minmax(0, 1fr));

    }



    .payment-filter-form {

        grid-template-columns: repeat(2, minmax(0, 1fr));

    }



    .payment-filter-actions {

        grid-column: 1 / -1;

    }

}



@media (max-width: 760px) {

    .payment-statistics-grid,

    .payment-filter-form {

        grid-template-columns: 1fr;

    }



    .payment-filter-actions {

        grid-column: auto;

        flex-wrap: wrap;

    }



    .payment-filter-actions .admin-button {

        flex: 1 1 150px;

    }



    .payment-record-count {

        margin-top: 10px;

    }



    .payment-pagination {

        align-items: flex-start;

        flex-direction: column;

    }

}



/* ============================================================

   ARIZONA ADMIN DASHBOARD VISUAL SYSTEM

\============================================================ */



.payment-dashboard-header {

    margin-bottom: 18px;

}



.payment-dashboard-header .admin-page-eyebrow,

.payment-records-panel .admin-panel-eyebrow,

.payment-filter-panel .admin-panel-eyebrow {

    color: #635bff;

    font-size: 9px;

    font-weight: 800;

    letter-spacing: .12em;

}



.payment-dashboard-header h2 {

    margin: 4px 0 4px;

    color: #0f172a;

    font-size: 24px;

    font-weight: 800;

    letter-spacing: -.025em;

}



.payment-dashboard-header p {

    color: #7b8497;

    font-size: 12px;

}



.payment-statistics-grid {

    gap: 14px;

    margin-bottom: 18px;

}



.payment-statistics-grid .admin-stat-card {

    min-height: 116px;

    padding: 17px;

    border: 1px solid #e6eaf1;

    border-radius: 11px;

    background: #fff;

    box-shadow: 0 1px 2px rgba(15, 23, 42, .02);

}



.payment-statistics-grid .admin-stat-label {

    color: #687386;

    font-size: 10px;

    font-weight: 600;

}



.payment-statistics-grid .admin-stat-value {

    display: block;

    margin-top: 13px;

    color: #111827;

    font-size: 24px;

    font-weight: 800;

    line-height: 1;

}



.payment-statistics-grid .admin-stat-icon {

    width: 34px;

    height: 34px;

    border: 0;

    border-radius: 9px;

    background: #eef0ff;

    color: #635bff;

    font-size: 13px;

}



.payment-statistics-grid .admin-stat-card:nth-child(2) .admin-stat-icon {

    background: #fff4df;

    color: #d88716;

}



.payment-statistics-grid .admin-stat-card:nth-child(3) .admin-stat-icon {

    background: #eaf8f1;

    color: #13875b;

}



.payment-statistics-grid .admin-stat-card:nth-child(4) .admin-stat-icon {

    background: #edf4ff;

    color: #3b73dc;

}



.payment-stat-note {

    margin-top: 11px;

    color: #635bff;

    font-size: 9px;

}



.payment-filter-panel,

.payment-records-panel {

    overflow: hidden;

    margin-bottom: 18px;

    border: 1px solid #e6eaf1;

    border-radius: 11px;

    background: #fff;

    box-shadow: none;

}



.payment-filter-panel .admin-panel-header,

.payment-records-panel .admin-panel-header {

    padding: 16px 18px;

    border-bottom: 1px solid #edf0f5;

}



.payment-filter-panel .admin-panel-header h3,

.payment-records-panel .admin-panel-header h3 {

    margin: 3px 0 0;

    color: #172033;

    font-size: 14px;

    font-weight: 800;

}



.payment-panel-icon {

    width: 34px;

    height: 34px;

    border: 0;

    border-radius: 9px;

    background: #eef0ff;

    color: #635bff;

    font-size: 12px;

}



.payment-filter-form {

    padding: 15px 18px 18px;

}



.payment-search-field input,

.payment-select-field select {

    min-height: 40px;

    border-color: #e0e5ed;

    border-radius: 7px;

    color: #344054;

    font-size: 11px;

}



.payment-filter-actions .admin-button,

.payment-dashboard-header .admin-button {

    min-height: 38px;

    border-radius: 7px;

    font-size: 10px;

}



.payment-filter-actions .admin-button-primary {

    border-color: #635bff;

    background: #635bff;

}



.payment-record-count {

    border: 0;

    background: #f3f5f8;

    color: #586174;

    font-size: 9px;

}



.payment-panel-description {

    color: #8a93a4;

    font-size: 10px;

}



.payment-table-wrapper {

    border-top: 0;

}



.payment-table thead {

    background: #fbfcfe;

}



.payment-table th {

    padding: 11px 13px;

    border-bottom: 1px solid #edf0f5;

    color: #7c8596;

    font-size: 8px;

    font-weight: 800;

    letter-spacing: .07em;

    text-transform: uppercase;

}



.payment-table td {

    padding: 12px 13px;

    border-bottom: 1px solid #f0f2f6;

    color: #3f4858;

    font-size: 10px;

}



.payment-order-icon {

    flex-basis: 30px;

    width: 30px;

    height: 30px;

    border: 0;

    border-radius: 7px;

    background: #eef0ff;

    color: #635bff;

    font-size: 10px;

}



.payment-customer-avatar {

    flex-basis: 30px;

    width: 30px;

    height: 30px;

    border: 0;

    background: #f0f2f6;

    color: #374151;

    font-size: 9px;

}



.payment-order-number,

.payment-customer-cell strong,

.payment-date-cell strong,

.payment-amount {

    color: #172033;

    font-size: 10px;

}



.payment-order-number {

    color: #5c56f5;

}



.payment-order-cell small,

.payment-customer-cell small,

.payment-date-cell small {

    color: #929aaa;

    font-size: 8px;

}



.payment-provider {

    font-size: 9px;

}



.payment-reference {

    max-width: 145px;

    padding: 4px 6px;

    background: #f5f6f8;

    color: #5e6675;

    font-size: 8px;

}



.payment-status-badge,

.verification-badge {

    gap: 4px;

    padding: 4px 7px;

    font-size: 8px;

}



.payment-status-pending,

.payment-status-partially_paid,

.verification-pending {

    background: #fff4dc;

    color: #a86a00;

}



.payment-status-paid,

.payment-status-completed,

.payment-status-succeeded,

.verification-verified {

    background: #e8f8ef;

    color: #148457;

}



.payment-status-failed,

.payment-status-declined,

.payment-status-cancelled,

.verification-rejected {

    background: #ffeded;

    color: #c13b3b;

}



.payment-review-button {

    min-height: 29px;

    padding: 5px 8px;

    border-color: #e2e6ed;

    border-radius: 6px;

    color: #5d57f4;

    font-size: 8px;

}



.payment-review-button:hover {

    border-color: #635bff;

    background: #635bff;

}



.payment-pagination {

    padding: 13px 18px;

    background: #fff;

}



.payment-pagination-summary {

    color: #8a93a4;

    font-size: 9px;

}



.payment-empty-state {

    min-height: 250px;

    justify-content: center;

}



.payment-empty-icon {

    width: 52px;

    height: 52px;

    border: 0;

    border-radius: 50%;

    background: #eef0ff;

    color: #635bff;

    font-size: 17px;

}



.payment-empty-state h3 {

    color: #172033;

    font-size: 13px;

}



.payment-empty-state p {

    color: #8a93a4;

    font-size: 10px;

}




.pv-popup{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:20px}.pv-popup[hidden]{display:none}
.pv-popup-backdrop{position:absolute;inset:0;border:0;background:rgba(15,23,42,.58)}
.pv-popup-card{position:relative;z-index:1;width:min(430px,100%);padding:25px;border:1px solid #e4e7ec;border-radius:14px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.22);text-align:center}
.pv-popup-close{position:absolute;top:12px;right:12px;width:32px;height:32px;border:0;border-radius:8px;background:#f3f4f6;color:#667085;cursor:pointer}
.pv-popup-icon{display:grid;place-items:center;width:48px;height:48px;margin:0 auto 12px;border-radius:13px;font-size:20px}.pv-popup-icon.success{background:#eaf8f1;color:#13875b}.pv-popup-icon.error{background:#fff0f0;color:#c13b3b}
.pv-popup-card h3{margin:5px 0 7px;color:#172033;font-size:18px}.pv-popup-card p{margin:0 0 16px;color:#667085;font-size:11px;line-height:1.6}.pv-popup-card ul{margin:0 0 16px;padding-left:20px;color:#a63b3b;text-align:left;font-size:10px;line-height:1.6}
.pv-confirm-actions{display:flex;gap:9px;justify-content:center;margin-top:17px}

.pv-pagination{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
    width:100%;
}
.pv-page-numbers{
    display:flex;
    align-items:center;
    gap:5px;
}
.pv-page-button,
.pv-page-number{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    min-height:34px;
    border:1px solid #e4e7ec;
    border-radius:8px;
    background:#fff;
    color:#344054;
    font-size:10px;
    font-weight:800;
    line-height:1;
    text-decoration:none;
    transition:border-color .15s ease,background .15s ease,color .15s ease,box-shadow .15s ease;
}
.pv-page-button{padding:0 11px;}
.pv-page-number{width:34px;padding:0;}
.pv-page-button i{font-size:9px;}
.pv-page-button:hover,
.pv-page-number:hover{
    border-color:#c7c3ff;
    background:#f7f6ff;
    color:#635bff;
}
.pv-page-number.is-active{
    border-color:#635bff;
    background:#635bff;
    color:#fff;
    box-shadow:0 3px 10px rgba(99,91,255,.18);
}
.pv-page-button.is-disabled{
    background:#f8fafc;
    color:#98a2b3;
    cursor:not-allowed;
}
@media(max-width:640px){
    .pv-pagination{justify-content:center;}
    .pv-page-button span{display:none;}
    .pv-page-button{width:34px;padding:0;}
}
</style>



<script>
document.addEventListener('DOMContentLoaded',function(){
 document.querySelectorAll('[data-pv-popup-close]').forEach(function(b){b.addEventListener('click',function(){var p=b.closest('.pv-popup');if(p)p.remove();});});
 document.addEventListener('keydown',function(e){if(e.key==='Escape'){var p=document.getElementById('paymentFeedbackPopup');if(p)p.remove();}});
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\payment-verifications\index.blade.php ENDPATH**/ ?>