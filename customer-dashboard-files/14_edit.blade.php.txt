@extends('admin.layouts.app')
@section('title','Edit Email Template')
@section('page-heading','Edit Email Template')
@section('content')
<div style="max-width:1000px;margin:auto;background:white;padding:24px;border:1px solid #e2e8f0;border-radius:16px">
<a href="{{ route('admin.email-templates.index') }}">Back to email templates</a>
<h2>{{ $emailTemplate->name }}</h2>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<ul role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('admin.email-templates.update',$emailTemplate) }}">@csrf @method('PUT')
<label for="subject">Subject</label><input id="subject" name="subject" maxlength="255" required value="{{ old('subject',$emailTemplate->subject) }}" style="display:block;width:100%;padding:12px;margin:8px 0 20px;box-sizing:border-box">
<label for="body">Email body (HTML)</label><textarea id="body" name="body" rows="20" required maxlength="50000" style="display:block;width:100%;padding:12px;box-sizing:border-box;font-family:monospace">{{ old('body',$emailTemplate->body) }}</textarea>
<p>Available variables: @foreach($emailTemplate->available_variables ?? [] as $variable)<code>{{ '{'.'{'.$variable.'}'.'}' }}</code> @endforeach</p>
<p>Basic HTML is supported. Unsafe tags and attributes are removed when rendering.</p>
<input type="hidden" name="is_enabled" value="0"><label><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled',$emailTemplate->is_enabled))> Use this custom template (otherwise use the built-in email)</label>
<p><button type="submit">Save template</button> <a href="{{ route('admin.email-templates.preview',$emailTemplate) }}" target="_blank" rel="noopener">Preview saved template</a></p>
</form>
<hr><h3>Send a test email</h3><form method="POST" action="{{ route('admin.email-templates.test',$emailTemplate) }}">@csrf
<label for="test-email">Recipient email</label><input type="email" name="email" id="test-email" required maxlength="255"><button type="submit">Send test email</button>
</form></div>
@endsection
