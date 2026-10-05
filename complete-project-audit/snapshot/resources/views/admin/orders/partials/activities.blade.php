@if ($order->activities->isNotEmpty())

    <div class="admin-order-timeline">

        @foreach ($order->activities as $activity)

            @php
                $activityIcon = match ($activity->type) {
                    'order_status_changed' =>
                        'fa-solid fa-box',

                    'payment_status_changed' =>
                        'fa-solid fa-credit-card',

                    'tracking_updated' =>
                        'fa-solid fa-truck',

                    'note_added' =>
                        'fa-solid fa-note-sticky',

                    'email_sent' =>
                        'fa-solid fa-envelope',

                    'invoice_generated' =>
                        'fa-solid fa-file-invoice',

                    default =>
                        'fa-solid fa-pen',
                };
            @endphp

            <article class="admin-order-timeline-item">

                <div class="admin-order-timeline-marker">
                    <i class="{{ $activityIcon }}"></i>
                </div>

                <div class="admin-order-timeline-content">

                    <div class="admin-order-timeline-heading">

                        <div>
                            <strong>
                                {{ $activity->title }}
                            </strong>

                            <small>
                                by {{ $activity->actor_name }}
                            </small>
                        </div>

                        <time
                            datetime="{{ $activity->created_at?->toIso8601String() }}"
                        >
                            {{ $activity->created_at?->format(
                                'M d, Y g:i A'
                            ) }}
                        </time>

                    </div>

                    @if ($activity->formatted_change)

                        <div class="admin-order-activity-change">

                            <span>
                                {{ $activity->old_value
                                    ?: 'Not set' }}
                            </span>

                            <i class="fa-solid fa-arrow-right"></i>

                            <strong>
                                {{ $activity->new_value
                                    ?: 'Not set' }}
                            </strong>

                        </div>

                    @endif

                    @if ($activity->description)

                        <p>
                            {!! nl2br(
                                e($activity->description)
                            ) !!}
                        </p>

                    @endif

                </div>

            </article>

        @endforeach

    </div>

@else

    <div class="admin-order-empty-section">

        <span>
            <i class="fa-solid fa-clock-rotate-left"></i>
        </span>

        <strong>No activity recorded yet</strong>

        <p>
            Status updates, payment changes, tracking changes and notes will
            appear here.
        </p>

    </div>

@endif