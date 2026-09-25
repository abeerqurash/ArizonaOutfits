<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class OrderActivityService
{
    public function record(
        Order $order,
        string $type,
        string $title,
        ?string $description = null,
        ?string $fieldName = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?array $metadata = null,
        ?Authenticatable $user = null
    ): OrderActivity {
        $actor = $user
            ?? Auth::guard('admin')->user()
            ?? Auth::guard('web')->user();

        $adminId = $actor instanceof Admin
            ? (int) $actor->getAuthIdentifier()
            : null;

        $userId = $actor instanceof User
            ? (int) $actor->getAuthIdentifier()
            : null;

        return $order->activities()->create([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'field_name' => $fieldName,
            'old_value' => $this->normalizeValue($oldValue),
            'new_value' => $this->normalizeValue($newValue),
            'metadata' => $metadata,
        ]);
    }

    public function orderStatusChanged(
        Order $order,
        mixed $oldStatus,
        mixed $newStatus
    ): OrderActivity {
        return $this->record(
            order: $order,
            type: OrderActivity::TYPE_ORDER_STATUS_CHANGED,
            title: 'Order status changed',
            description: sprintf(
                'Order status changed from %s to %s.',
                $this->humanize($oldStatus),
                $this->humanize($newStatus)
            ),
            fieldName: 'order_status',
            oldValue: $oldStatus,
            newValue: $newStatus
        );
    }

    public function paymentStatusChanged(
        Order $order,
        mixed $oldStatus,
        mixed $newStatus
    ): OrderActivity {
        return $this->record(
            order: $order,
            type: OrderActivity::TYPE_PAYMENT_STATUS_CHANGED,
            title: 'Payment status changed',
            description: sprintf(
                'Payment status changed from %s to %s.',
                $this->humanize($oldStatus),
                $this->humanize($newStatus)
            ),
            fieldName: 'payment_status',
            oldValue: $oldStatus,
            newValue: $newStatus
        );
    }

    public function trackingUpdated(
        Order $order,
        mixed $oldTrackingNumber,
        mixed $newTrackingNumber
    ): OrderActivity {
        return $this->record(
            order: $order,
            type: OrderActivity::TYPE_TRACKING_UPDATED,
            title: 'Tracking information updated',
            description: filled($newTrackingNumber)
                ? 'A tracking number was added or updated.'
                : 'The tracking number was removed.',
            fieldName: 'tracking_number',
            oldValue: $oldTrackingNumber,
            newValue: $newTrackingNumber
        );
    }

    public function noteAdded(
        Order $order,
        string $note,
        bool $customerVisible = false
    ): OrderActivity {
        return $this->record(
            order: $order,
            type: OrderActivity::TYPE_NOTE_ADDED,
            title: $customerVisible
                ? 'Customer note added'
                : 'Internal note added',
            description: $note,
            metadata: [
                'customer_visible' => $customerVisible,
            ]
        );
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return trim((string) $value);
    }

    private function humanize(mixed $value): string
    {
        if (blank($value)) {
            return 'Not set';
        }

        return ucwords(
            str_replace(
                ['_', '-'],
                ' ',
                (string) $value
            )
        );
    }
}
