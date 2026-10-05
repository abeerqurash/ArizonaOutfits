@extends('layouts.app')

@section('title', 'Track Your Order')

@section('content')
@php
    $trackedOrder = $order ?? null;

    $statusSteps = [
        'pending' => 1,
        'confirmed' => 2,
        'processing' => 2,
        'packed' => 3,
        'shipped' => 4,
        'out_for_delivery' => 5,
        'completed' => 6,
        'delivered' => 6,
    ];

    $currentStatus = strtolower((string) ($trackedOrder?->order_status ?? 'pending'));
    $currentStep = $statusSteps[$currentStatus] ?? 1;
    $isStopped = in_array($currentStatus, ['cancelled', 'refunded'], true);
@endphp

<style>
    .order-track-page{padding:52px 20px 72px;background:#f7f8fb;min-height:70vh}
    .order-track-wrap{max-width:1100px;margin:0 auto}
    .order-track-hero{text-align:center;max-width:700px;margin:0 auto 28px}
    .order-track-eyebrow{display:inline-block;margin-bottom:8px;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0f766e}
    .order-track-hero h1{margin:0 0 10px;color:#172033;font-size:clamp(30px,5vw,46px);line-height:1.08}
    .order-track-hero p{margin:0;color:#667085;font-size:15px;line-height:1.7}
    .order-track-card{background:#fff;border:1px solid #e5eaf1;border-radius:16px;box-shadow:0 8px 30px rgba(23,32,51,.06);padding:26px;margin-bottom:22px}
    .order-track-form{display:grid;grid-template-columns:1fr 1fr auto;gap:14px;align-items:end}
    .order-track-field label{display:block;margin-bottom:7px;color:#344054;font-size:13px;font-weight:700}
    .order-track-field input{width:100%;height:48px;padding:0 15px;border:1px solid #d0d5dd;border-radius:10px;background:#fff;color:#172033;font-size:14px;outline:none}
    .order-track-field input:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.1)}
    .order-track-button{min-height:48px;border:0}

    .order-track-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .order-track-summary-item{padding:16px;border:1px solid #e5eaf1;border-radius:12px;background:#fafbfc}
    .order-track-summary-item span{display:block;margin-bottom:5px;color:#667085;font-size:12px}
    .order-track-summary-item strong{display:block;color:#172033;font-size:14px;overflow-wrap:anywhere}
    .order-track-section-title{margin:0 0 18px;color:#172033;font-size:20px}
    .order-track-alert{padding:14px 16px;border-radius:12px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:14px;margin-bottom:18px}
    .order-track-steps{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;position:relative}
    .order-track-step{text-align:center;position:relative}
    .order-track-step::before{content:"";position:absolute;top:18px;left:-50%;width:100%;height:2px;background:#e5eaf1;z-index:0}
    .order-track-step:first-child::before{display:none}
    .order-track-step.is-done::before{background:#0f766e}
    .order-track-dot{position:relative;z-index:1;width:36px;height:36px;margin:0 auto 9px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eef2f6;color:#667085;border:2px solid #e5eaf1;font-size:13px}
    .order-track-step.is-done .order-track-dot{background:#0f766e;border-color:#0f766e;color:#fff}
    .order-track-step span{display:block;color:#667085;font-size:11px;font-weight:700;line-height:1.35}
    .order-track-step.is-done span{color:#172033}
    .order-track-details{display:grid;grid-template-columns:1fr 1fr;gap:22px}
    .order-track-info{display:grid;gap:12px}
    .order-track-info-row{display:flex;justify-content:space-between;gap:18px;padding-bottom:12px;border-bottom:1px solid #eef1f5;font-size:13px}
    .order-track-info-row:last-child{border-bottom:0;padding-bottom:0}
    .order-track-info-row span{color:#667085}
    .order-track-info-row strong{color:#172033;text-align:right;overflow-wrap:anywhere}
    .order-track-history{display:grid;gap:12px}
    .order-track-event{position:relative;padding:0 0 14px 22px;border-left:2px solid #e5eaf1}
    .order-track-event:last-child{padding-bottom:0}
    .order-track-event::before{content:"";position:absolute;left:-6px;top:3px;width:10px;height:10px;border-radius:50%;background:#0f766e}
    .order-track-event strong{display:block;color:#172033;font-size:13px;margin-bottom:3px}
    .order-track-event p{margin:0 0 4px;color:#667085;font-size:12px;line-height:1.5}
    .order-track-event time{color:#98a2b3;font-size:11px}
    .order-track-empty{color:#667085;font-size:13px}
    @media(max-width:800px){
        .order-track-form{grid-template-columns:1fr}
        .order-track-button{width:100%}
        .order-track-summary{grid-template-columns:1fr 1fr}
        .order-track-details{grid-template-columns:1fr}
        .order-track-steps{grid-template-columns:repeat(3,1fr);row-gap:20px}
        .order-track-step::before{display:none}
    }
    @media(max-width:480px){
        .order-track-page{padding:34px 14px 54px}
        .order-track-card{padding:18px}
        .order-track-summary{grid-template-columns:1fr}
        .order-track-steps{grid-template-columns:repeat(2,1fr)}
    }
</style>

<main class="order-track-page">
    <div class="order-track-wrap">
        <header class="order-track-hero">
            <span class="order-track-eyebrow">Order tracking</span>
            <h1>Track your order</h1>
            <p>Enter your order number and the email address used at checkout to securely view the latest delivery information.</p>
        </header>

        <section class="order-track-card" aria-labelledby="track-form-heading">
            <h2 id="track-form-heading" class="order-track-section-title">Find your order</h2>

            <form method="POST" action="{{ route('orders.track.lookup') }}" class="order-track-form">
                @csrf

                <div class="order-track-field">
                    <label for="order_number">Order number</label>
                    <input
                        id="order_number"
                        name="order_number"
                        type="text"
                        value="{{ old('order_number', $trackedOrder?->order_number) }}"
                        placeholder="AO-XXXXXXXXXX"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="order-track-field">
                    <label for="email">Email address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="order-track-button btn-style-2 fs-12 text-color-white cursor-pointer">
                    <div
                        class="button-text text-uppercase letter-space-3px"
                        style="transform: translate3d(0px, 0px, 0px) scale(1);">
                        Track order
                    </div>
                </button>
            </form>

            @php
                $trackingPopupMessage = $errors->first('tracking')
                    ?: $errors->first('order_number')
                    ?: $errors->first('email');
            @endphp

            @if($trackingPopupMessage)
                <div
                    id="orderTrackingPopupMessage"
                    data-message="{{ $trackingPopupMessage }}"
                    hidden></div>
            @endif
        </section>

        @if($trackedOrder)
            <section class="order-track-card">
                <h2 class="order-track-section-title">Order overview</h2>

                @if($isStopped)
                    <div class="order-track-alert">
                        This order is currently {{ str($currentStatus)->headline() }}.
                    </div>
                @endif

                <div class="order-track-summary">
                    <div class="order-track-summary-item">
                        <span>Order number</span>
                        <strong>{{ $trackedOrder->order_number }}</strong>
                    </div>
                    <div class="order-track-summary-item">
                        <span>Order status</span>
                        <strong>{{ str($currentStatus)->headline() }}</strong>
                    </div>
                    <div class="order-track-summary-item">
                        <span>Tracking number</span>
                        <strong>{{ $trackedOrder->tracking_number ?: 'Tracking pending' }}</strong>
                    </div>
                    <div class="order-track-summary-item">
                        <span>Estimated delivery</span>
                        <strong>{{ $trackedOrder->estimated_delivery ?: 'To be confirmed' }}</strong>
                    </div>
                </div>
            </section>

            @unless($isStopped)
                <section class="order-track-card">
                    <h2 class="order-track-section-title">Delivery progress</h2>

                    <div class="order-track-steps" aria-label="Order delivery progress">
                        @foreach([
                            1 => ['Pending', 'fa-receipt'],
                            2 => ['Processing', 'fa-box-open'],
                            3 => ['Packed', 'fa-box'],
                            4 => ['Shipped', 'fa-truck-fast'],
                            5 => ['Out for delivery', 'fa-route'],
                            6 => ['Delivered', 'fa-circle-check'],
                        ] as $step => [$label, $icon])
                            <div class="order-track-step {{ $currentStep >= $step ? 'is-done' : '' }}">
                                <div class="order-track-dot">
                                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                </div>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endunless

            <div class="order-track-details">
                <section class="order-track-card">
                    <h2 class="order-track-section-title">Delivery details</h2>

                    <div class="order-track-info">
                        <div class="order-track-info-row">
                            <span>Shipping method</span>
                            <strong>{{ $trackedOrder->shipping_method ?: 'To be confirmed' }}</strong>
                        </div>
                        <div class="order-track-info-row">
                            <span>Destination</span>
                            <strong>
                                {{ collect([
                                    $trackedOrder->shipping_city ?: $trackedOrder->billing_city,
                                    $trackedOrder->shipping_state ?: $trackedOrder->billing_state,
                                    $trackedOrder->shipping_country ?: $trackedOrder->billing_country,
                                ])->filter()->implode(', ') ?: 'Not available' }}
                            </strong>
                        </div>
                        <div class="order-track-info-row">
                            <span>Placed</span>
                            <strong>{{ $trackedOrder->created_at?->format('d M Y, h:i A') ?: 'Not available' }}</strong>
                        </div>
                        <div class="order-track-info-row">
                            <span>Payment status</span>
                            <strong>{{ str($trackedOrder->payment_status ?: 'pending')->headline() }}</strong>
                        </div>
                    </div>
                </section>

                <section class="order-track-card">
                    <h2 class="order-track-section-title">Tracking history</h2>

                    @if($trackedOrder->activities->isNotEmpty())
                        <div class="order-track-history">
                            @foreach($trackedOrder->activities as $activity)
                                <article class="order-track-event">
                                    <strong>{{ $activity->title }}</strong>

                                    @if(filled($activity->description))
                                        <p>{{ $activity->description }}</p>
                                    @endif

                                    <time datetime="{{ $activity->created_at?->toIso8601String() }}">
                                        {{ $activity->created_at?->format('d M Y, h:i A') }}
                                    </time>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="order-track-empty">
                            No tracking updates have been recorded yet. Your current order status is
                            <strong>{{ str($currentStatus)->headline() }}</strong>.
                        </p>
                    @endif
                </section>
            </div>
        @endif
    </div>
</main>

<div
    class="order-track-popup"
    id="orderTrackPopup"
    role="dialog"
    aria-modal="true"
    aria-labelledby="orderTrackPopupTitle"
    aria-describedby="orderTrackPopupText"
    hidden>
    <button
        type="button"
        class="order-track-popup-backdrop"
        data-order-track-popup-close
        aria-label="Close notification"></button>

    <div class="order-track-popup-card">
        <div class="order-track-popup-icon">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        </div>

        <h2 id="orderTrackPopupTitle">Unable to find order</h2>
        <p id="orderTrackPopupText"></p>

        <button
            type="button"
            class="btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
            data-order-track-popup-close>
            <div
                class="button-text text-uppercase letter-space-3px"
                style="transform: translate3d(0px, 0px, 0px) scale(1);">
                Try again
            </div>
        </button>
    </div>
</div>

<style>
    .order-track-popup[hidden]{display:none!important}
    .order-track-popup{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px}
    .order-track-popup-backdrop{position:absolute;inset:0;width:100%;height:100%;border:0;background:rgba(15,23,42,.55);cursor:pointer}
    .order-track-popup-card{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;justify-content:center;width:min(100%,430px);padding:30px 26px;text-align:center;background:#fff;border:1px solid #e5eaf1;border-radius:18px;box-shadow:0 24px 70px rgba(15,23,42,.2)}
    .order-track-popup-card>*{text-align:center}
    .order-track-popup-icon{display:flex;align-items:center;justify-content:center;width:54px;height:54px;margin:0 auto 16px;border-radius:50%;background:#fff1f2;color:#b42318;font-size:22px}
    .order-track-popup-card h2{margin:0 0 9px;color:#172033;font-size:22px}
    .order-track-popup-card p{margin:0 0 22px;color:#667085;font-size:14px;line-height:1.65}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    document.querySelectorAll(
        '.order-track-page .btn-style-1, .order-track-page .btn-style-2, .order-track-page .btn-style-3, ' +
        '.order-track-popup .btn-style-1, .order-track-popup .btn-style-2, .order-track-popup .btn-style-3'
    ).forEach(function (button) {
        if (button.dataset.movingTextReady === 'true') {
            return;
        }

        const buttonText = button.querySelector('.button-text');

        if (!buttonText) {
            return;
        }

        button.dataset.movingTextReady = 'true';

        button.addEventListener('mousemove', function (event) {
            const rect = button.getBoundingClientRect();
            const x = event.clientX - rect.left - (rect.width / 2);
            const y = event.clientY - rect.top - (rect.height / 2);

            buttonText.style.transform =
                `translate3d(${x / 6}px, ${y / 6}px, 0) scale(1.12)`;
        });

        button.addEventListener('mouseleave', function () {
            buttonText.style.transform =
                'translate3d(0, 0, 0) scale(1)';
        });
    });

    const popupMessage = document.getElementById('orderTrackingPopupMessage');
    const popup = document.getElementById('orderTrackPopup');
    const popupText = document.getElementById('orderTrackPopupText');

    if (popupMessage && popup && popupText) {
        popupText.textContent = popupMessage.dataset.message || 'Please check your order details and try again.';
        popup.hidden = false;
        document.body.style.overflow = 'hidden';

        const closePopup = function () {
            popup.hidden = true;
            document.body.style.overflow = '';
        };

        popup.querySelectorAll('[data-order-track-popup-close]').forEach(function (control) {
            control.addEventListener('click', closePopup);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !popup.hidden) {
                closePopup();
            }
        });
    }
});
</script>

@endsection
