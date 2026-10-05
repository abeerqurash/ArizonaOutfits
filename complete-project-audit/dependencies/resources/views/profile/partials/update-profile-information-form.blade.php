<header class="customer-profile-card-header">
    <div class="customer-profile-card-icon" aria-hidden="true">
        <i class="fa-solid fa-user"></i>
    </div>

    <div>
        <span>Personal details</span>

        <h3>Profile information</h3>

        <p>
            Keep your personal details up to date. Email and phone changes
            are protected through Login &amp; Security.
        </p>
    </div>
</header>

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    <label class="customer-field">
        <span>Full name</span>

        <input
            type="text"
            name="name"
            value="{{ old('name', $user->name) }}"
            required
            maxlength="255"
            autocomplete="name"
        >

        @error('name')
            <small>{{ $message }}</small>
        @enderror
    </label>

    <div class="customer-security-field">
        <div class="customer-security-field-heading">
            <span>Email address</span>

            @if (filled($user->email))
                <span class="customer-security-badge">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Security managed
                </span>
            @endif
        </div>

        <div class="customer-locked-input">
            <i class="fa-regular fa-envelope" aria-hidden="true"></i>

            <input
                type="email"
                value="{{ $user->email }}"
                placeholder="No email address added"
                autocomplete="email"
                readonly
                aria-readonly="true"
            >

            <i class="fa-solid fa-lock customer-locked-input-lock" aria-hidden="true"></i>
        </div>

        <small class="customer-field-help">
            Change and verify your email from Login &amp; Security.
        </small>
    </div>

    <div class="customer-security-field">
        <div class="customer-security-field-heading">
            <span>Phone number</span>

            @if (filled($user->phone))
                <span class="customer-security-badge">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Security managed
                </span>
            @endif
        </div>

        <div class="customer-locked-input">
            <i class="fa-solid fa-phone" aria-hidden="true"></i>

            <input
                type="tel"
                value="{{ $user->phone }}"
                placeholder="No phone number added"
                autocomplete="tel"
                readonly
                aria-readonly="true"
            >

            <i class="fa-solid fa-lock customer-locked-input-lock" aria-hidden="true"></i>
        </div>

        <small class="customer-field-help">
            Change and verify your phone number from Login &amp; Security.
        </small>
    </div>

    @if (
        ! auth()->user()->is_admin
        && ! auth()->user()->is_super_admin
    )
        <a
            href="{{ route('customer.security') }}"
            class="customer-security-action"
        >
            <span class="customer-security-action-icon">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            </span>

            <span class="customer-security-action-copy">
                <strong>Manage Login &amp; Security</strong>
                <small>
                    Change email, phone number, password and connected sign-in methods.
                </small>
            </span>

            <i class="fa-solid fa-chevron-right customer-security-action-arrow" aria-hidden="true"></i>
        </a>
    @endif

    <div class="customer-form-actions customer-profile-form-actions">
        <button type="submit">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
            Save profile
        </button>

        @if (session('status') === 'profile-updated')
            <em>
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                Profile saved.
            </em>
        @endif
    </div>
</form>

<style>
.customer-profile-card-header{
    display:flex;
    align-items:flex-start;
    gap:13px;
}

.customer-profile-card-icon{
    display:grid;
    flex:0 0 40px;
    width:40px;
    height:40px;
    place-items:center;
    border-radius:10px;
    background:#dbeafe;
    color:#1d4ed8;
    font-size:15px;
}

.customer-security-field{
    display:grid;
    gap:6px;
    margin-bottom:14px;
}

.customer-security-field-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}

.customer-security-field-heading>span:first-child{
    color:#475569;
    font-size:11px;
    font-weight:850;
}

.customer-security-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 7px;
    border-radius:999px;
    background:#ecfdf5;
    color:#047857;
    font-size:9px;
    font-weight:900;
    letter-spacing:.02em;
    text-transform:uppercase;
}

.customer-locked-input{
    position:relative;
    display:flex;
    align-items:center;
}

.customer-locked-input>i:first-child{
    position:absolute;
    left:12px;
    z-index:1;
    color:#94a3b8;
    font-size:13px;
    pointer-events:none;
}

.customer-locked-input input{
    width:100%;
    min-height:42px;
    box-sizing:border-box;
    padding:9px 38px 9px 35px;
    border:1px solid #e2e8f0;
    border-radius:8px;
    outline:0;
    background:#f8fafc;
    color:#475569;
    font:inherit;
    cursor:default;
}

.customer-locked-input input::placeholder{
    color:#94a3b8;
}

.customer-locked-input-lock{
    position:absolute;
    right:12px;
    color:#94a3b8;
    font-size:11px;
    pointer-events:none;
}

.customer-field-help{
    color:#94a3b8;
    font-size:10px;
    line-height:1.5;
}

.customer-security-action{
    display:flex;
    align-items:center;
    gap:11px;
    margin:18px 0;
    padding:12px;
    border:1px solid #e5eaf1;
    border-radius:10px;
    background:#f8fafc;
    color:#172033;
    text-decoration:none;
    transition:
        border-color .18s ease,
        background .18s ease,
        transform .18s ease;
}

.customer-security-action:hover{
    border-color:#cbd5e1;
    background:#fff;
    transform:translateY(-1px);
}

.customer-security-action-icon{
    display:grid;
    flex:0 0 36px;
    width:36px;
    height:36px;
    place-items:center;
    border-radius:9px;
    background:#ecfdf5;
    color:#047857;
    font-size:13px;
}

.customer-security-action-copy{
    display:block;
    min-width:0;
    flex:1;
}

.customer-security-action-copy strong,
.customer-security-action-copy small{
    display:block;
}

.customer-security-action-copy strong{
    color:#172033;
    font-size:12px;
    font-weight:850;
}

.customer-security-action-copy small{
    margin-top:3px;
    color:#64748b;
    font-size:10px;
    line-height:1.45;
}

.customer-security-action-arrow{
    color:#94a3b8;
    font-size:10px;
}

.customer-profile-form-actions{
    padding-top:4px;
}

.customer-profile-form-actions button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
}

.customer-profile-form-actions em{
    display:inline-flex;
    align-items:center;
    gap:5px;
}

@media(max-width:480px){
    .customer-security-field-heading{
        align-items:flex-start;
        flex-direction:column;
        gap:5px;
    }

    .customer-security-action{
        align-items:flex-start;
    }

    .customer-security-action-arrow{
        margin-top:12px;
    }

    .customer-profile-form-actions{
        align-items:stretch;
        flex-direction:column;
    }

    .customer-profile-form-actions button{
        width:100%;
    }
}
</style>
