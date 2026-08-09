<header><h2>Profile information</h2><p>Update your name, email address and phone number.</p></header>

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')
    <div class="auth-field"><label for="profile_name">Full name</label><input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<p class="auth-error">{{ $message }}</p>@enderror</div>
    <div class="auth-field"><label for="profile_email">Email address</label><input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
    <div class="auth-field"><label for="profile_phone">Phone number</label><input id="profile_phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">@error('phone')<p class="auth-error">{{ $message }}</p>@enderror</div>
    <button class="auth-submit" type="submit">Save profile</button>
    @if(session('status') === 'profile-updated')<span class="auth-status">Profile saved.</span>@endif
</form>
