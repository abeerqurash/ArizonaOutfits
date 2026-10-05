@extends('admin.layouts.app')

@section('title', 'Add Supplier')

@section('content')

<div class="supplier-editor-page">

    {{-- Page Header --}}
    <section class="supplier-editor-header">

        <div class="supplier-editor-header-content">

            <div class="supplier-editor-header-icon">

                <i class="fa-solid fa-truck-field"></i>

            </div>

            <div>

                <span class="supplier-editor-eyebrow">
                    Supplier management
                </span>

                <h1>
                    Add Supplier
                </h1>

                <p>
                    Create a new supplier record with contact, commercial,
                    banking and purchasing information.
                </p>

            </div>

        </div>

        <div class="supplier-editor-header-actions">

            <a
                href="{{ route('admin.suppliers.index') }}"
                class="supplier-editor-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                Back to Suppliers

            </a>

            <a
                href="{{ route('admin.purchase-orders.index') }}"
                class="supplier-editor-button purchase">

                <i class="fa-solid fa-file-invoice-dollar"></i>

                Purchase Orders

            </a>

        </div>

    </section>

    {{-- Validation Errors --}}
    @if ($errors->any())

        <section class="supplier-validation-summary">

            <span class="supplier-validation-icon">

                <i class="fa-solid fa-circle-exclamation"></i>

            </span>

            <div>

                <h2>
                    Please correct the following information
                </h2>

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </section>

    @endif

    <form
        method="POST"
        action="{{ route('admin.suppliers.store') }}"
        id="supplierEditorForm">

        @csrf

        @include(
            'admin.suppliers.form',
            [
                'supplier' => null,
                'submitLabel' => 'Create Supplier',
            ]
        )

    </form>

</div>

@endsection

@include('admin.suppliers.partials.editor-styles')

@include('admin.suppliers.partials.editor-scripts')