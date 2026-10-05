@if ($order->notes->isNotEmpty())

    <div class="admin-order-notes-list">

        @foreach ($order->notes as $note)

            <article
                class="admin-order-note-card"
                data-note-id="{{ $note->id }}"
            >

                <div class="admin-order-note-header">

                    <div class="admin-order-note-author">

                        <span class="admin-order-note-avatar">
                            {{ strtoupper(
                                substr(
                                    $note->author_name,
                                    0,
                                    1
                                )
                            ) }}
                        </span>

                        <div>
                            <strong>
                                {{ $note->author_name }}
                            </strong>

                            <small>
                                {{ $note->created_at?->format(
                                    'M d, Y \a\t g:i A'
                                ) }}
                            </small>
                        </div>

                    </div>

                    <div class="admin-order-note-actions">

                        <span
                            class="admin-order-note-visibility {{
                                $note->is_customer_visible
                                    ? 'is-visible'
                                    : 'is-internal'
                            }}"
                        >
                            <i class="fa-solid {{
                                $note->is_customer_visible
                                    ? 'fa-eye'
                                    : 'fa-lock'
                            }}"></i>

                            {{ $note->is_customer_visible
                                ? 'Customer visible'
                                : 'Internal' }}
                        </span>

                        <button
                            type="button"
                            class="admin-order-note-delete"
                            data-delete-note-url="{{
                                route(
                                    'admin.orders.notes.destroy',
                                    [
                                        'order' => $order,
                                        'note' => $note,
                                    ]
                                )
                            }}"
                            aria-label="Delete note"
                            title="Delete note"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>

                </div>

                <div class="admin-order-note-text">
                    {!! nl2br(e($note->note)) !!}
                </div>

            </article>

        @endforeach

    </div>

@else

    <div class="admin-order-empty-section">

        <span>
            <i class="fa-regular fa-note-sticky"></i>
        </span>

        <strong>No notes yet</strong>

        <p>
            Add an internal note or a note visible to the customer.
        </p>

    </div>

@endif