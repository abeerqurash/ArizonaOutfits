@extends('customer.layouts.app')

@section('title', 'Verify Phone')

@section('page-heading', 'Verify Phone')

@push('page-styles')

@include('customer.partials.styles')

@endpush


@section('content')

<header class="customer-page-heading">

    <div>

        <span>
            Account security
        </span>

        <h2>
            Verify phone number
        </h2>

        <p>
            Enter the 6-digit verification code sent to your phone.
        </p>

    </div>

</header>


<section class="customer-panel customer-form-card">

    @if (session('status'))

        <div>
            {{ session('status') }}
        </div>

    @endif


    @if ($errors->any())

        <div>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <p>
        Verification code sent to:
    </p>

    <p>
        <strong>
            {{ $phone }}
        </strong>
    </p>


    <form
        method="POST"
        action="{{ route('customer.security.phone.verify.store') }}"
    >

        @csrf


        <div class="auth-field">

            <label for="code">
                Verification code
            </label>

            <input
                id="code"
                type="text"
                name="code"
                required
                autofocus
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                pattern="[0-9]{6}"
                placeholder="000000"
            >

        </div>


        <button
            type="submit"
            class="auth-submit"
        >
            Verify phone
        </button>

    </form>


    <p style="margin-top:20px;">

        <a href="{{ route('customer.security') }}">
            Back to Login & Security
        </a>

    </p>

</section>

@endsection