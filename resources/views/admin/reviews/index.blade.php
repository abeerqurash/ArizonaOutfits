@extends('layouts.app')

@section('title', 'Manage Reviews')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div class="admin-page-heading">
                    <div>
                        <h1>Manage Reviews</h1>
                        <p>Approve, reject or delete product reviews.</p>
                    </div>

                    <a href="{{ route('admin.dashboard') }}">
                        Back to Dashboard
                    </a>
                </div>

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form
                    action="{{ route('admin.reviews.index') }}"
                    method="GET"
                    class="admin-filter-form"
                >

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Customer, product or review"
                    >

                    <select name="status">
                        <option value="">All statuses</option>

                        @foreach ([
                            'pending',
                            'approved',
                            'rejected'
                        ] as $status)

                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>

                        @endforeach
                    </select>

                    <select name="rating">
                        <option value="">All ratings</option>

                        @for ($rating = 5; $rating >= 1; $rating--)

                            <option
                                value="{{ $rating }}"
                                @selected(
                                    (string) request('rating')
                                    === (string) $rating
                                )
                            >
                                {{ $rating }} Stars
                            </option>

                        @endfor
                    </select>

                    <button type="submit">
                        Filter Reviews
                    </button>

                    <a href="{{ route('admin.reviews.index') }}">
                        Reset
                    </a>

                </form>

                <div class="admin-review-list">

                    @forelse ($reviews as $review)

                        <article class="admin-review-card">

                            <div class="admin-review-heading">

                                <div>
                                    <h3>
                                        {{ $review->title
                                            ?: 'Product Review' }}
                                    </h3>

                                    <p>
                                        Product:
                                        <strong>
                                            {{ $review->product?->title
                                                ?: 'Deleted Product' }}
                                        </strong>
                                    </p>
                                </div>

                                <span class="admin-status admin-status-{{
                                    $review->status
                                }}">
                                    {{ ucfirst(
                                        $review->status
                                        ?: 'pending'
                                    ) }}
                                </span>

                            </div>

                            <div class="rating-stars">
                                @for ($star = 1; $star <= 5; $star++)

                                    <span class="{{
                                        $star <= $review->rating
                                            ? 'filled'
                                            : ''
                                    }}">
                                        ★
                                    </span>

                                @endfor
                            </div>

                            <p>
                                <strong>Customer:</strong>

                                {{ $review->name
                                    ?: $review->user?->name
                                    ?: 'Guest' }}

                                @if ($review->email || $review->user?->email)
                                    — {{ $review->email
                                        ?: $review->user?->email }}
                                @endif
                            </p>

                            <p>
                                {{ $review->review }}
                            </p>

                            <small>
                                Submitted
                                {{ $review->created_at?->format(
                                    'F j, Y \a\t g:i A'
                                ) }}
                            </small>

                            <div class="admin-review-actions">

                                <form
                                    action="{{ route(
                                        'admin.reviews.update',
                                        $review
                                    ) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="approved"
                                    >

                                    <button type="submit">
                                        Approve
                                    </button>
                                </form>

                                <form
                                    action="{{ route(
                                        'admin.reviews.update',
                                        $review
                                    ) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="rejected"
                                    >

                                    <button type="submit">
                                        Reject
                                    </button>
                                </form>

                                <form
                                    action="{{ route(
                                        'admin.reviews.update',
                                        $review
                                    ) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="pending"
                                    >

                                    <button type="submit">
                                        Pending
                                    </button>
                                </form>

                                <form
                                    action="{{ route(
                                        'admin.reviews.destroy',
                                        $review
                                    ) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this review permanently?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="admin-danger-button"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>

                        </article>

                    @empty

                        <p>No reviews were found.</p>

                    @endforelse

                </div>

                {{ $reviews->links() }}

            </div>

        </div>
    </div>

</div>

@endsection