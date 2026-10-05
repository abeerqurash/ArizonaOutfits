<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Customer feedback
            </span>

            <h3>
                Latest Reviews
            </h3>
        </div>

        <a href="{{ route('admin.reviews.index') }}">
            View all reviews
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="admin-table-wrapper">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($latestReviews as $review)

                @php
                $reviewStatus = strtolower(
                $review->status ?? 'pending'
                );

                $reviewerName =
                $review->name
                ?: $review->user?->name
                ?: 'Guest';

                $reviewProduct =
                $review->product?->title
                ?: 'Product unavailable';
                @endphp

                <tr>

                    <td>
                        <strong>
                            {{ $reviewerName }}
                        </strong>

                        @if ($review->email)
                        <small>
                            {{ $review->email }}
                        </small>
                        @endif
                    </td>

                    <td>
                        {{ $reviewProduct }}
                    </td>

                    <td>
                        <strong>
                            {{ number_format(
                                                (int) $review->rating
                                            ) }}/5
                        </strong>

                        <small>
                            @for ($star = 1; $star <= 5; $star++)
                                @if ($star <=(int) $review->rating)
                                ★
                                @else
                                ☆
                                @endif
                                @endfor
                        </small>
                    </td>

                    <td>
                        <span
                            class="admin-badge admin-badge-{{
                                                $reviewStatus
                                            }}">
                            {{ ucfirst($reviewStatus) }}
                        </span>
                    </td>

                    <td>
                        <span class="admin-table-date">
                            {{
                                                $review->created_at?->format(
                                                    'M d, Y'
                                                )
                                            }}
                        </span>
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="5">

                        <div class="admin-empty-state">

                            <span>
                                <i class="fa-solid fa-star"></i>
                            </span>

                            <h4>
                                No reviews yet
                            </h4>

                            <p>
                                Customer reviews will appear here.
                            </p>

                        </div>

                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>