<header><span>Password</span><h3>Update password</h3><p>Use a long, unique password to keep your account secure.</p></header>
<form method="POST" action="{{ route('password.update') }}">@csrf @method('PUT')
<label class="customer-field"><span>Current password</span><input name="current_password" type="password" autocomplete="current-password">@foreach($errors->updatePassword->get('current_password') as $message)<small>{{ $message }}</small>@endforeach</label>
<label class="customer-field"><span>New password</span><input name="password" type="password" autocomplete="new-password">@foreach($errors->updatePassword->get('password') as $message)<small>{{ $message }}</small>@endforeach</label>
<label class="customer-field"><span>Confirm new password</span><input name="password_confirmation" type="password" autocomplete="new-password">@foreach($errors->updatePassword->get('password_confirmation') as $message)<small>{{ $message }}</small>@endforeach</label>
<div class="customer-form-actions"><button type="submit">Save password</button>@if(session('status') === 'password-updated')<em>Password updated.</em>@endif</div></form>
