@extends('admin.layouts.app')
@section('title','Create CMS Page')
@section('page-heading','Create CMS Page')
@section('content')<div class="page-editor"><header class="editor-heading"><a href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-arrow-left"></i> CMS Pages</a><h2>Create a new page</h2><p>Draft it privately or publish it immediately.</p></header>@include('admin.pages._form')</div>@endsection
