@extends('admin.layouts.app')

@section('title', 'Edit Blog Category')
@section('page-heading', 'Blog Categories')

@section('content')
    @include('admin.categories._form')
@endsection
