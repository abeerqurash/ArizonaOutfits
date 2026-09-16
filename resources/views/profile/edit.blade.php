@extends(
    auth()->user()?->is_admin
        ? 'admin.layouts.app'
        : 'customer.layouts.app'
)

@section('title', 'My Profile')

@section('page-heading', 'Profile & Security')


@push('page-styles')

@include('customer.partials.styles')

@endpush


@section('content')

<header class="customer-page-heading">

    <div>

        <span>
            Account settings
        </span>

        <h2>
            My profile
        </h2>

        <p>
            Manage your personal information and account security.
        </p>

    </div>

</header>


@if (
    ! auth()->user()->is_admin
    && ! auth()->user()->is_super_admin
)

    <section
        class="customer-panel customer-form-card"
        style="margin-bottom:20px;"
    >

        <h3>
            Login & Security
        </h3>

        <p>
            Manage your email, verified phone number,
            password, Google and Facebook connections.
        </p>

        <a
            href="{{ route('customer.security') }}"
            class="auth-submit"
        >
            Manage Login & Security
        </a>

    </section>

@endif


<div class="customer-profile-grid">

    <section class="customer-panel customer-form-card">

        @include(
            'profile.partials.update-profile-information-form'
        )

    </section>


    @if (filled(auth()->user()->password))

        <section class="customer-panel customer-form-card">

            @include(
                'profile.partials.update-password-form'
            )

        </section>

    @endif


    <section
        class="customer-panel customer-form-card danger"
    >

        @include(
            'profile.partials.delete-user-form'
        )

    </section>

</div>

@endsection