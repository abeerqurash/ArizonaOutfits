@extends('admin.layouts.app')

@section('title', 'Manage Reviews')

@section('page-heading', 'Reviews')

@section('content')
<div class="admin-reviews-page">

    <div class="admin-page-header">
        <div>
            <div class="admin-page-eyebrow">CUSTOMER FEEDBACK</div>
            <h1>Product Reviews</h1>
            <p>Moderate customer feedback, ratings and product reviews.</p>
        </div>

        <div class="admin-page-actions">
            <a href="{{ route('admin.dashboard') }}" class="admin-button admin-button-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-review-toast admin-review-toast-success" data-review-toast>
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button type="button" aria-label="Close notification" data-review-toast-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="admin-review-toast admin-review-toast-error" data-review-toast>
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
            <button type="button" aria-label="Close notification" data-review-toast-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <div class="review-stat-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon review-stat-total">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <span>Total Reviews</span>
                <strong>{{ number_format($reviewStats['total']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon review-stat-pending">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <span>Pending</span>
                <strong>{{ number_format($reviewStats['pending']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon review-stat-approved">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Approved</span>
                <strong>{{ number_format($reviewStats['approved']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon review-stat-rejected">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div>
                <span>Rejected</span>
                <strong>{{ number_format($reviewStats['rejected']) }}</strong>
            </div>
        </div>
    </div>

    <section class="admin-panel review-filter-panel">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="review-filter-form">
            <div class="review-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customer, product or review..."
                    aria-label="Search reviews"
                >
            </div>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <select name="rating" aria-label="Filter by rating">
                <option value="">All ratings</option>
                @for ($rating = 5; $rating >= 1; $rating--)
                    <option
                        value="{{ $rating }}"
                        @selected((string) request('rating') === (string) $rating)
                    >
                        {{ $rating }} {{ $rating === 1 ? 'Star' : 'Stars' }}
                    </option>
                @endfor
            </select>

            <button type="submit" class="admin-button admin-button-primary">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>

            @if (request()->filled('search') || request()->filled('status') || request()->filled('rating'))
                <a href="{{ route('admin.reviews.index') }}" class="admin-button admin-button-secondary">
                    Reset
                </a>
            @endif
        </form>
    </section>

    <section class="review-grid">
        @forelse ($reviews as $review)
            @php
                $product = $review->product;
                $productImage = $product?->featured_image_url;
                $reviewerName = $review->name ?: $review->user?->name ?: 'Guest Customer';
                $reviewerEmail = $review->email ?: $review->user?->email;
                $status = $review->status ?: 'pending';
                $rating = max(0, min(5, (int) $review->rating));
            @endphp

            <article class="review-card">
                <div class="review-product-media">
                    @if ($productImage)
                        <img
                            src="{{ $productImage }}"
                            alt="{{ $product?->title ?: 'Reviewed product' }}"
                            loading="lazy"
                        >
                    @else
                        <div class="review-product-placeholder">
                            <i class="fa-solid fa-image"></i>
                            <span>No product image</span>
                        </div>
                    @endif

                    <span class="review-status review-status-{{ $status }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>

                <div class="review-card-body">
                    <div class="review-product-name">
                        <span>PRODUCT</span>
                        <strong>{{ $product?->title ?: 'Deleted Product' }}</strong>
                    </div>

                    <div class="review-rating" aria-label="{{ $rating }} out of 5 stars">
                        @for ($star = 1; $star <= 5; $star++)
                            <i class="{{ $star <= $rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                        @endfor
                        <span>{{ $rating }}/5</span>
                    </div>

                    <h2>Product Review</h2>

                    <p class="review-copy">{{ $review->review }}</p>

                    <div class="reviewer">
                        <div class="reviewer-avatar">
                            {{ strtoupper(mb_substr($reviewerName, 0, 1)) }}
                        </div>

                        <div class="reviewer-details">
                            <strong>{{ $reviewerName }}</strong>

                            @if ($reviewerEmail)
                                <span>{{ $reviewerEmail }}</span>
                            @else
                                <span>Guest review</span>
                            @endif
                        </div>

                        <time datetime="{{ optional($review->created_at)->toAtomString() }}">
                            {{ $review->created_at?->format('M j, Y') }}
                        </time>
                    </div>
                </div>

                <div class="review-card-actions">
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="approved">

                        <button
                            type="submit"
                            class="review-action review-action-approve"
                            @disabled($status === 'approved')
                        >
                            <i class="fa-solid fa-check"></i>
                            Approved
                        </button>
                    </form>

                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="pending">

                        <button
                            type="submit"
                            class="review-action review-action-pending"
                            @disabled($status === 'pending')
                        >
                            <i class="fa-regular fa-clock"></i>
                            Pending
                        </button>
                    </form>

                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">

                        <button
                            type="submit"
                            class="review-action review-action-reject"
                            @disabled($status === 'rejected')
                        >
                            <i class="fa-solid fa-ban"></i>
                            Rejected
                        </button>
                    </form>

                    <button
                        type="button"
                        class="review-action review-action-delete"
                        data-review-delete
                        data-review-id="{{ $review->id }}"
                        data-delete-url="{{ route('admin.reviews.destroy', $review) }}"
                    >
                        <i class="fa-regular fa-trash-can"></i>
                        Delete
                    </button>
                </div>
            </article>
        @empty
            <div class="admin-panel review-empty">
                <div class="review-empty-icon">
                    <i class="fa-regular fa-message"></i>
                </div>
                <h2>No reviews found</h2>
                <p>There are no reviews matching the selected filters.</p>
            </div>
        @endforelse
    </section>

    @if ($reviews->hasPages())
        <div class="review-pagination">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

<div class="review-modal-backdrop" data-review-modal hidden>
    <div class="review-modal" role="dialog" aria-modal="true" aria-labelledby="review-delete-title">
        <button type="button" class="review-modal-close" data-review-modal-cancel aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="review-modal-icon">
            <i class="fa-regular fa-trash-can"></i>
        </div>

        <div class="admin-page-eyebrow">PERMANENT ACTION</div>
        <h2 id="review-delete-title">Delete this review?</h2>
        <p>
            <strong data-review-modal-name>Product Review</strong> will be permanently removed.
            This action cannot be undone.
        </p>

        <div class="review-modal-actions">
            <button type="button" class="admin-button admin-button-secondary" data-review-modal-cancel>
                Cancel
            </button>

            <form method="POST" data-review-delete-form>
                @csrf
                @method('DELETE')

                <button type="submit" class="admin-button review-delete-confirm">
                    <i class="fa-regular fa-trash-can"></i>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

<style>
.admin-reviews-page{display:flex;flex-direction:column;gap:16px}
.admin-page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
.admin-page-header h1{margin:3px 0 4px;font-size:24px;line-height:1.2;color:#101828}
.admin-page-header p{margin:0;color:#667085;font-size:13px}
.admin-page-eyebrow{font-size:10px;font-weight:800;letter-spacing:.13em;color:#635bff}
.admin-page-actions{display:flex;gap:8px;flex-wrap:wrap}
.admin-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 12px;border:1px solid #dfe3ea;border-radius:10px;background:#fff;color:#344054;text-decoration:none;font-size:12px;font-weight:700;cursor:pointer}
.admin-button-primary{background:#635bff;border-color:#635bff;color:#fff}
.admin-button-secondary{background:#fff}

.review-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.admin-stat-card{display:flex;align-items:center;gap:10px;min-height:76px;background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:12px 14px}
.admin-stat-icon{width:38px;height:38px;flex:0 0 38px;border-radius:10px;display:grid;place-items:center;font-size:15px}
.admin-stat-card span{display:block;color:#667085;font-size:11px;font-weight:700}
.admin-stat-card strong{display:block;margin-top:1px;font-size:19px;line-height:1.2;color:#101828}
.review-stat-total{background:#f0efff;color:#635bff}
.review-stat-pending{background:#fff7e6;color:#b76e00}
.review-stat-approved{background:#ecfdf3;color:#027a48}
.review-stat-rejected{background:#fff1f3;color:#c01048}

.admin-panel{background:#fff;border:1px solid #e4e7ec;border-radius:12px}
.review-filter-panel{padding:10px}
.review-filter-form{display:grid;grid-template-columns:minmax(220px,1fr) 155px 140px auto auto;gap:8px}
.review-search{position:relative}
.review-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#98a2b3;font-size:12px}
.review-filter-form input,.review-filter-form select{width:100%;height:36px;border:1px solid #dfe3ea;border-radius:9px;background:#fff;color:#344054;font-size:12px;outline:none}
.review-filter-form input{padding:0 11px 0 32px}
.review-filter-form select{padding:0 9px}
.review-filter-form input:focus,.review-filter-form select:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}

.review-grid{display:flex;flex-direction:column;gap:10px}
.review-card{display:grid;grid-template-columns:118px minmax(0,1fr) 126px;min-height:150px;overflow:hidden;background:#fff;border:1px solid #e4e7ec;border-radius:12px}
.review-product-media{position:relative;min-height:150px;background:#f2f4f7;overflow:hidden;border-right:1px solid #eef0f3}
.review-product-media img{width:100%;height:100%;min-height:150px;object-fit:cover;display:block}
.review-product-placeholder{height:100%;min-height:150px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:#98a2b3}
.review-product-placeholder i{font-size:22px}
.review-product-placeholder span{font-size:10px;font-weight:700}
.review-status{position:absolute;left:8px;top:8px;padding:4px 7px;border-radius:999px;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;box-shadow:0 1px 3px rgba(16,24,40,.08)}
.review-status-pending{background:#fff7e6;color:#9a5b00}
.review-status-approved{background:#ecfdf3;color:#027a48}
.review-status-rejected{background:#fff1f3;color:#c01048}

.review-card-body{min-width:0;padding:13px 15px}
.review-product-name{display:flex;align-items:center;gap:7px;margin-bottom:6px}
.review-product-name span{font-size:9px;font-weight:800;letter-spacing:.1em;color:#98a2b3}
.review-product-name strong{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#344054}
.review-rating{display:flex;align-items:center;gap:2px;color:#f5a524;font-size:11px;margin-bottom:7px}
.review-rating span{margin-left:4px;color:#667085;font-size:10px;font-weight:700}
.review-card-body h2{margin:0 0 5px;color:#101828;font-size:15px;line-height:1.3}
.review-copy{display:-webkit-box;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:3;margin:0;color:#475467;font-size:12px;line-height:1.55}
.reviewer{display:grid;grid-template-columns:30px minmax(0,1fr) auto;gap:8px;align-items:center;margin-top:10px;padding-top:9px;border-top:1px solid #f0f1f3}
.reviewer-avatar{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:#f0efff;color:#635bff;font-weight:800;font-size:11px}
.reviewer-details{min-width:0}
.reviewer-details strong,.reviewer-details span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.reviewer-details strong{font-size:11px;color:#101828}
.reviewer-details span,.reviewer time{font-size:10px;color:#98a2b3}

.review-card-actions{display:flex;flex-direction:column;justify-content:center;gap:6px;padding:12px;border-left:1px solid #eef0f3;background:#fcfcfd}
.review-card-actions form{width:100%}
.review-action{width:100%;height:30px;display:inline-flex;align-items:center;justify-content:flex-start;gap:6px;padding:0 9px;border:1px solid #dfe3ea;border-radius:8px;background:#fff;color:#475467;font-size:10px;font-weight:800;cursor:pointer}
.review-action:disabled{background:#f9fafb;opacity:.55;cursor:default}
.review-action-approve{color:#027a48}
.review-action-pending{color:#9a5b00}
.review-action-reject,.review-action-delete{color:#c01048}

.review-empty{text-align:center;padding:44px 20px}
.review-empty-icon{width:44px;height:44px;margin:0 auto 10px;border-radius:11px;display:grid;place-items:center;background:#f0efff;color:#635bff;font-size:17px}
.review-empty h2{margin:0 0 4px;font-size:16px}
.review-empty p{margin:0;color:#667085;font-size:12px}
.review-pagination{display:flex;justify-content:center}

.admin-review-toast{position:fixed;right:22px;top:22px;z-index:1200;max-width:390px;display:flex;align-items:center;gap:8px;padding:11px 13px;border:1px solid;border-radius:10px;background:#fff;box-shadow:0 12px 30px rgba(16,24,40,.13);font-size:12px;font-weight:700}
.admin-review-toast-success{border-color:#abefc6;color:#027a48}
.admin-review-toast-error{border-color:#fecdca;color:#b42318}
.admin-review-toast button{margin-left:auto;border:0;background:transparent;color:inherit;cursor:pointer}

.review-modal-backdrop{position:fixed;inset:0;z-index:1300;background:rgba(15,23,42,.52);display:grid;place-items:center;padding:20px}
.review-modal-backdrop[hidden]{display:none}
.review-modal{position:relative;width:min(420px,100%);background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:22px;box-shadow:0 24px 70px rgba(15,23,42,.22)}
.review-modal-close{position:absolute;right:13px;top:13px;border:0;background:transparent;color:#667085;font-size:15px;cursor:pointer}
.review-modal-icon{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;background:#fff1f3;color:#c01048;font-size:16px;margin-bottom:12px}
.review-modal h2{margin:4px 0 7px;font-size:19px;color:#101828}
.review-modal p{margin:0;color:#667085;font-size:12px;line-height:1.55}
.review-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
.review-delete-confirm{background:#d92d20;border-color:#d92d20;color:#fff}

@media(max-width:1050px){
.review-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.review-filter-form{grid-template-columns:1fr 1fr 1fr}
.review-search{grid-column:1/-1}
.review-card{grid-template-columns:105px minmax(0,1fr)}
.review-card-actions{grid-column:1/-1;display:grid;grid-template-columns:repeat(4,1fr);border-left:0;border-top:1px solid #eef0f3}
}
@media(max-width:700px){
.admin-page-header{flex-direction:column}
.review-card{grid-template-columns:90px minmax(0,1fr)}
.review-product-media,.review-product-media img,.review-product-placeholder{min-height:140px}
.review-card-actions{grid-template-columns:1fr 1fr}
}
@media(max-width:560px){
.review-stat-grid{grid-template-columns:1fr}
.review-filter-form{grid-template-columns:1fr}
.review-search{grid-column:auto}
.review-card{display:block}
.review-product-media{height:190px;border-right:0;border-bottom:1px solid #eef0f3}
.review-product-media img{height:190px}
.review-card-actions{display:grid;grid-template-columns:1fr 1fr}
.reviewer{grid-template-columns:30px minmax(0,1fr)}
.reviewer time{grid-column:2}
.admin-review-toast{left:12px;right:12px;top:12px}
.review-modal-actions{flex-direction:column-reverse}
.review-modal-actions .admin-button,.review-modal-actions form,.review-modal-actions form button{width:100%}
}
</style>

<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.querySelector('[data-review-modal]');
    const deleteForm = document.querySelector('[data-review-delete-form]');
    const modalName = document.querySelector('[data-review-modal-name]');
    const deleteButtons = document.querySelectorAll('[data-review-delete]');
    const cancelButtons = document.querySelectorAll('[data-review-modal-cancel]');

    function closeDeleteModal() {
        if (!modal) {
            return;
        }

        modal.hidden = true;

        if (deleteForm) {
            deleteForm.removeAttribute('action');
        }
    }

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!modal || !deleteForm) {
                return;
            }

            deleteForm.action = button.dataset.deleteUrl || '';

            if (modalName) {
                modalName.textContent = button.dataset.reviewName || 'Product Review';
            }

            modal.hidden = false;
        });
    });

    cancelButtons.forEach(function (button) {
        button.addEventListener('click', closeDeleteModal);
    });

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeDeleteModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeDeleteModal();
        }
    });

    document.querySelectorAll('[data-review-toast]').forEach(function (toast) {
        const closeButton = toast.querySelector('[data-review-toast-close]');

        if (closeButton) {
            closeButton.addEventListener('click', function () {
                toast.remove();
            });
        }

        window.setTimeout(function () {
            if (toast.isConnected) {
                toast.remove();
            }
        }, 4500);
    });
});
</script>
