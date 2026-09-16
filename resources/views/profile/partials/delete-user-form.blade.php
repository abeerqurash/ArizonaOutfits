<header>
    <span>Danger zone</span>

    <h3>Delete account</h3>

    <p>
        This permanently removes your account.
        Completed order records may be retained where legally required.
    </p>
</header>

@if (filled(auth()->user()->password))

<details class="customer-delete-account">

    <summary>
        Delete my account
    </summary>

    <form
        method="POST"
        action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')

        <p>
            Enter your password to confirm permanent account deletion.
        </p>

        <label class="customer-field">

            <span>
                Password
            </span>

            <input
                name="password"
                type="password"
                autocomplete="current-password"
                required>

            @foreach ($errors->userDeletion->get('password') as $message)

            <small>
                {{ $message }}
            </small>

            @endforeach

        </label>

        <button type="submit">
            Permanently delete account
        </button>

    </form>

</details>

@else

<div
    class="admin-alert admin-alert-warning"
    role="alert">

    <i class="fa-solid fa-shield-halved"></i>

    <div>

        <strong>
            Additional verification required
        </strong>

        <p>
            This account does not currently have a password.
            Add and verify an email and password before requesting
            permanent account deletion.
        </p>

        <a
            href="{{ route('customer.security') }}"
            class="customer-primary-button">
            <i class="fa-solid fa-shield-halved"></i>

            Login & Security
        </a>

    </div>

</div>

@endif