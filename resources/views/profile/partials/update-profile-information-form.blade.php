<header><span>Personal details</span><h3>Profile information</h3><p>Update your name, email address and phone number.</p></header>
<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
<label class="customer-field"><span>Full name</span><input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<small>{{ $message }}</small>@enderror</label>
<label class="customer-field"><span>Email address</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">@error('email')<small>{{ $message }}</small>@enderror</label>
<label class="customer-field"><span>Phone number</span><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">@error('phone')<small>{{ $message }}</small>@enderror</label>
<div class="customer-form-actions"><button type="submit">Save profile</button>@if(session('status') === 'profile-updated')<em>Profile saved.</em>@endif</div></form>
