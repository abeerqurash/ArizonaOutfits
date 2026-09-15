<div class="auth-social-section">

    {{-- Social authentication error --}}
    @if (session('social_error'))
        <div class="auth-social-error" role="alert">
            {{ session('social_error') }}
        </div>
    @endif


    {{-- Google --}}
    <a
        href="{{ route('social.redirect', ['provider' => 'google']) }}"
        class="auth-social-button auth-social-google"
    >
        <span class="auth-social-icon" aria-hidden="true">
            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill="#4285F4"
                    d="M21.6 12.227c0-.709-.064-1.391-.182-2.045H12v3.868h5.382a4.6 4.6 0 0 1-1.995 3.018v2.509h3.232c1.891-1.741 2.981-4.309 2.981-7.35Z"
                />

                <path
                    fill="#34A853"
                    d="M12 22c2.7 0 4.964-.895 6.618-2.423l-3.232-2.509c-.895.6-2.041.955-3.386.955-2.605 0-4.809-1.759-5.6-4.123H3.059v2.591A10 10 0 0 0 12 22Z"
                />

                <path
                    fill="#FBBC05"
                    d="M6.4 13.9A6.01 6.01 0 0 1 6.086 12c0-.659.114-1.3.314-1.9V7.509H3.059A10 10 0 0 0 2 12c0 1.614.386 3.141 1.059 4.491L6.4 13.9Z"
                />

                <path
                    fill="#EA4335"
                    d="M12 5.977c1.468 0 2.786.505 3.823 1.496l2.868-2.868C16.959 2.991 14.695 2 12 2a10 10 0 0 0-8.941 5.509L6.4 10.1C7.191 7.736 9.395 5.977 12 5.977Z"
                />
            </svg>
        </span>

        <span>
            Continue with Google
        </span>
    </a>


    {{-- Facebook --}}
    <a
        href="{{ route('social.redirect', ['provider' => 'facebook']) }}"
        class="auth-social-button auth-social-facebook"
    >
        <span class="auth-social-icon" aria-hidden="true">

            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill="currentColor"
                    d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.024 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.971h-1.513c-1.49 0-1.956.931-1.956 1.887v2.263h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073Z"
                />
            </svg>

        </span>

        <span>
            Continue with Facebook
        </span>
    </a>


    {{-- Divider --}}
    <div class="auth-social-divider">
        <span></span>

        <strong>
            or continue with email
        </strong>

        <span></span>
    </div>

</div>