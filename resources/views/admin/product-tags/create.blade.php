@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Create Product Tag</h1>

                    <form action="{{ route('admin.product-tags.store') }}" method="POST">
                        @csrf

                        <input type="text" name="title" placeholder="Title" required>
                        <br><br>

                        <input type="text" name="slug" placeholder="Slug">
                        <br><br>

                        <button type="submit">Save Tag</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection