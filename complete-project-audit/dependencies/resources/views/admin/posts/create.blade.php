@extends('admin.layouts.app')

@section('title', 'Create Post')
@section('page-heading', 'Posts')

@section('content')
    @include('admin.posts._form')
@endsection
