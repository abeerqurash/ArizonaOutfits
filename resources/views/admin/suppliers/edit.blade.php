@extends('admin.layouts.app')

@section('title', 'Edit ' . $supplier->company_name)

@section('content')

<div class="supplier-editor-page">

    {{-- Page Header --}}
    <section class="supplier-editor-header">

        <div class="supplier-editor-header-content">

            <div class="supplier-editor-header-icon">

                <i class="fa-regular fa-pen-to-square"></i>

            </div>

            <div>

                <span class="supplier-editor-eyebrow">
                    Supplier management
                </span>

                <div class="supplier-editor-title-row">

                    <h1>
                        Edit Supplier
                    </h1>

                    <span
                        class="supplier-editor-status {{
                            $supplier->status
                        }}">

                        {{ $supplier->status_label }}

                    </span>

                    @if ($supplier->is_preferred)

                        <span class="supplier-editor-preferred">

                            <i class="fa-solid fa-star"></i>

                            Preferred

                        </span>

                    @endif

                </div>

                <p>
                    Update the supplier account for
                    <strong>
                        {{ $supplier->company_name }}
                    </strong>.
                </p>

            </div>

        </div>

        <div class="supplier-editor-header-actions">

            <a
                href="{{ route(
                    'admin.suppliers.show',
                    $supplier
                ) }}"
                class="supplier-editor-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                Supplier Profile

            </a>

            <a
                href="{{ route('admin.suppliers.index') }}"
                class="supplier-editor-button purchase">

                <i class="fa-solid fa-list"></i>

                All Suppliers

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
        action="{{ route(
            'admin.suppliers.update',
            $supplier
        ) }}"
        id="supplierEditorForm">

        @csrf
        @method('PUT')

        @include(
            'admin.suppliers.form',
            [
                'supplier' => $supplier,
                'submitLabel' => 'Update Supplier',
            ]
        )

    </form>

</div>

@endsection

@include('admin.suppliers.partials.editor-styles')

@include('admin.suppliers.partials.editor-scripts')