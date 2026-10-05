@extends(
    auth()->user()?->is_admin
        ? 'admin.layouts.app'
        : 'customer.layouts.app'
)

@section('title', 'My Profile')

@section('page-heading', 'Profile & Security')


@push('page-styles')

@include('customer.partials.styles')

<style>
.profile-security-card{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
    padding:18px 20px;
}

.profile-security-main{
    display:flex;
    align-items:center;
    min-width:0;
    gap:14px;
}

.profile-security-icon{
    display:grid;
    flex:0 0 44px;
    width:44px;
    height:44px;
    place-items:center;
    border-radius:11px;
    background:#ecfdf5;
    color:#047857;
    font-size:16px;
}

.profile-security-copy{
    min-width:0;
}

.profile-security-copy>span{
    display:block;
    margin-bottom:4px;
    color:#0f766e;
    font-size:10px;
    font-weight:900;
    letter-spacing:.1em;
    text-transform:uppercase;
}

.profile-security-copy h3{
    margin:0;
    color:#172033;
    font-size:16px;
    line-height:1.3;
}

.profile-security-copy p{
    margin:5px 0 0;
    color:#64748b;
    font-size:12px;
    line-height:1.55;
}

.profile-security-features{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:9px;
}

.profile-security-feature{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 7px;
    border-radius:999px;
    background:#f1f5f9;
    color:#475569;
    font-size:9px;
    font-weight:800;
}

.profile-security-feature i{
    color:#0f766e;
    font-size:9px;
}

.profile-security-button{
    display:inline-flex;
    flex:0 0 auto;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:40px;
    padding:0 14px;
    border-radius:8px;
    background:#172033;
    color:#fff;
    font-size:11px;
    font-weight:850;
    text-decoration:none;
    transition:
        transform .18s ease,
        background .18s ease,
        box-shadow .18s ease;
}

.profile-security-button:hover{
    background:#0f172a;
    color:#fff;
    transform:translateY(-1px);
    box-shadow:0 7px 16px rgba(15,23,42,.12);
}

.profile-security-button i:last-child{
    font-size:9px;
    opacity:.7;
}

@media(max-width:760px){
    .profile-security-card{
        align-items:stretch;
        flex-direction:column;
    }

    .profile-security-button{
        width:100%;
        box-sizing:border-box;
    }
}

@media(max-width:480px){
    .profile-security-main{
        align-items:flex-start;
    }

    .profile-security-features{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .profile-security-feature{
        justify-content:center;
        border-radius:7px;
        text-align:center;
    }
}
</style>

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

    <section class="customer-panel profile-security-card">

        <div class="profile-security-main">

            <div class="profile-security-icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="profile-security-copy">

                <span>
                    Account protection
                </span>

                <h3>
                    Login &amp; Security
                </h3>

                <p>
                    Manage your verified contact details, password
                    and connected sign-in methods.
                </p>

                <div class="profile-security-features">

                    <span class="profile-security-feature">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        Email
                    </span>

                    <span class="profile-security-feature">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        Phone
                    </span>

                    <span class="profile-security-feature">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                        Password
                    </span>

                    <span class="profile-security-feature">
                        <i class="fa-solid fa-link" aria-hidden="true"></i>
                        Connected accounts
                    </span>

                </div>

            </div>

        </div>

        <a
            href="{{ route('customer.security') }}"
            class="profile-security-button"
        >
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>

            Manage Security

            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
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
