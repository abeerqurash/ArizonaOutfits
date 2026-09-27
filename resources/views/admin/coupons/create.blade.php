@extends('admin.layouts.app')
@section('title','Create Coupon')
@section('page-heading','Create Coupon')
@section('content')
<div class="admin-page-header coupon-page-head"><div><span class="admin-page-eyebrow">Promotions</span><h2>Create Coupon</h2><p>Build a discount with precise validity, usage and catalog targeting rules.</p></div><div class="admin-page-actions"><a href="{{ route('admin.coupons.index') }}" class="admin-button admin-button-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Coupons</a></div></div>
<form action="{{ route('admin.coupons.store') }}" method="POST" novalidate>@csrf @include('admin.coupons.partials.form',['coupon'=>null])</form>
@endsection
@push('page-styles')<style>.coupon-page-head{margin-bottom:18px}.coupon-page-head h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800}.coupon-page-head p{color:#7b8497;font-size:12px}</style>@endpush
