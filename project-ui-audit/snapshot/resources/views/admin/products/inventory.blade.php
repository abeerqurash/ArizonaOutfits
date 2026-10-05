@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">
            <div class="container">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">

                    <div>
                        <h1 style="margin:0;">
                            Inventory Management
                        </h1>

                        <p style="margin:8px 0 0;color:#666;">
                            {{ $product->title }}
                        </p>

                        @if($product->sku)
                            <small style="color:#888;">
                                SKU : {{ $product->sku }}
                            </small>
                        @endif
                    </div>

                    <a
                        href="{{ route('admin.products.index') }}"
                        style="
                            background:#444;
                            color:#fff;
                            padding:10px 18px;
                            border-radius:5px;
                            text-decoration:none;
                        "
                    >
                        ← Back to Products
                    </a>

                </div>

                @if(session('success'))

                    <div
                        style="
                            background:#d4edda;
                            color:#155724;
                            border:1px solid #c3e6cb;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        {{ session('success') }}
                    </div>

                @endif

                @if(session('error'))

                    <div
                        style="
                            background:#f8d7da;
                            color:#721c24;
                            border:1px solid #f5c6cb;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        {{ session('error') }}
                    </div>

                @endif

                @if($errors->any())

                    <div
                        style="
                            background:#fff3cd;
                            color:#856404;
                            border:1px solid #ffeeba;
                            padding:15px;
                            border-radius:5px;
                            margin-bottom:20px;
                        "
                    >
                        <ul style="margin:0;padding-left:20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                @endif


                @if($product->variants->count())

                    <h2 style="margin-bottom:20px;">
                        Product Variants
                    </h2>

                    @foreach($product->variants as $variant)

                        <form
                            method="POST"
                            action="{{ route('admin.products.inventory.update',$product) }}"
                            style="
                                border:1px solid #ddd;
                                border-radius:8px;
                                padding:20px;
                                margin-bottom:20px;
                            "
                        >

                            @csrf

                            <input
                                type="hidden"
                                name="variant_id"
                                value="{{ $variant->id }}"
                            >

                            <h3 style="margin-top:0;">

                                {{ collect($variant->options)
                                    ->pluck('value_label')
                                    ->implode(' / ') }}

                            </h3>

                            <p>

                                Current Stock :

                                <strong>

                                    {{ $variant->stock }}

                                </strong>

                            </p>

                            <div
                                style="
                                    display:grid;
                                    grid-template-columns:repeat(4,1fr);
                                    gap:15px;
                                "
                            >

                                <select
                                    name="adjustment_type"
                                    required
                                >
                                    <option value="add">
                                        Add Stock
                                    </option>

                                    <option value="remove">
                                        Remove Stock
                                    </option>

                                    <option value="set">
                                        Set Exact Stock
                                    </option>
                                </select>

                                <input
                                    type="number"
                                    name="quantity"
                                    min="0"
                                    required
                                    placeholder="Quantity"
                                >

                                <input
                                    type="text"
                                    name="reason"
                                    required
                                    placeholder="Reason"
                                >

                                <button
                                    type="submit"
                                    style="
                                        background:#0d6efd;
                                        color:#fff;
                                        border:none;
                                        border-radius:5px;
                                        cursor:pointer;
                                    "
                                >
                                    Save
                                </button>

                            </div>

                            <textarea
                                name="notes"
                                rows="3"
                                placeholder="Notes (optional)"
                                style="
                                    width:100%;
                                    margin-top:15px;
                                "
                            ></textarea>

                        </form>

                    @endforeach

                @else

                    <form
                        method="POST"
                        action="{{ route('admin.products.inventory.update',$product) }}"
                        style="
                            border:1px solid #ddd;
                            border-radius:8px;
                            padding:25px;
                        "
                    >

                        @csrf

                        <h2 style="margin-top:0;">
                            Current Stock :
                            {{ $product->stock }}
                        </h2>

                        <div
                            style="
                                display:grid;
                                grid-template-columns:repeat(4,1fr);
                                gap:15px;
                            "
                        >

                            <select
                                name="adjustment_type"
                                required
                            >
                                <option value="add">
                                    Add Stock
                                </option>

                                <option value="remove">
                                    Remove Stock
                                </option>

                                <option value="set">
                                    Set Exact Stock
                                </option>
                            </select>

                            <input
                                type="number"
                                name="quantity"
                                min="0"
                                required
                                placeholder="Quantity"
                            >

                            <input
                                type="text"
                                name="reason"
                                required
                                placeholder="Reason"
                            >

                            <button
                                type="submit"
                                style="
                                    background:#198754;
                                    color:#fff;
                                    border:none;
                                    border-radius:5px;
                                    cursor:pointer;
                                "
                            >
                                Save Adjustment
                            </button>

                        </div>

                        <textarea
                            name="notes"
                            rows="4"
                            placeholder="Notes"
                            style="
                                width:100%;
                                margin-top:20px;
                            "
                        ></textarea>

                    </form>

                @endif


                <div style="margin-top:50px;">

                    <h2>
                        Recent Inventory History
                    </h2>

                    <table
                        width="100%"
                        border="1"
                        cellspacing="0"
                        cellpadding="10"
                        style="
                            border-collapse:collapse;
                            margin-top:20px;
                        "
                    >

                        <thead>

                            <tr>

                                <th>Date</th>

                                <th>Movement</th>

                                <th>Before</th>

                                <th>After</th>

                                <th>Change</th>

                                <th>Reason</th>

                                <th>Performed By</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($product->inventoryHistories()->latest()->take(20)->get() as $history)

                                <tr>

                                    <td>

                                        {{ $history->created_at->format('d M Y H:i') }}

                                    </td>

                                    <td>

                                        {{ $history->movement_label }}

                                    </td>

                                    <td>

                                        {{ $history->stock_before }}

                                    </td>

                                    <td>

                                        {{ $history->stock_after }}

                                    </td>

                                    <td>

                                        {{ $history->formatted_quantity_change }}

                                    </td>

                                    <td>

                                        {{ $history->reason }}

                                    </td>

                                    <td>

                                        {{ $history->performed_by }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7">

                                        No inventory history found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

</div>

@endsection