@extends('admin.layouts.app')

@section('title', 'Edit Post')
@section('page-heading', 'Posts')

@section('content')
    @include('admin.posts._form')

    {{-- Separate forms keep restore independent of the main edit form. --}}
    @foreach($post->revisions as $revision)
        <form id="restore-revision-{{ $revision->id }}" method="POST"
            action="{{ route('admin.posts.revisions.restore', ['post' => $post, 'revision' => $revision]) }}"
            onsubmit="return confirm('Restore this saved revision? Your current saved version will be kept in revision history. Unsaved edits will be discarded.');">
            @csrf
            @method('PATCH')
        </form>
    @endforeach
@endsection
