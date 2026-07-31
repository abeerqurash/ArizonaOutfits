<?php

namespace App\Services;

use App\Mail\AdminNewOrderMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderNotificationLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderEmailService
{
    public const CUSTOMER_CONFIRMATION =
        'customer_order_confirmation';

    public const ADMIN_NEW_ORDER =
        'admin_new_order';

    public function sendOrderEmails(Order $order): void
    {
        $this->sendCustomerConfirmation($order);
        $this->sendAdminNotification($order);
    }

    public function sendCustomerConfirmation(
        Order $order
    ): void {
        $email = $this->customerEmail($order);

        if (!$email) {
            Log::warning(
                'Customer order email was not sent because '
                . 'the order has no customer email address.',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            );

            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::CUSTOMER_CONFIRMATION,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(
                    new CustomerOrderConfirmationMail($order)
                );
            }
        );
    }

    public function sendAdminNotification(
        Order $order
    ): void {
        $email = config('mail.admin_order_email');

        if (!$email) {
            Log::warning(
                'Admin order email was not sent because '
                . 'ADMIN_ORDER_EMAIL is not configured.',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            );

            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::ADMIN_NEW_ORDER,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(
                    new AdminNewOrderMail($order)
                );
            }
        );
    }

    private function sendOnce(
        Order $order,
        string $type,
        string $recipient,
        callable $callback
    ): void {
        $recipient = strtolower(trim($recipient));

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning(
                'Order notification recipient is invalid.',
                [
                    'order_id' => $order->id,
                    'notification_type' => $type,
                    'recipient' => $recipient,
                ]
            );

            return;
        }

        try {
            $log = OrderNotificationLog::query()
                ->firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'notification_type' => $type,
                        'recipient_email' => $recipient,
                    ],
                    [
                        'sent_at' => null,
                        'failure_message' => null,
                    ]
                );
        } catch (QueryException $exception) {
            /*
             * Another request may have created the same unique
             * notification log at the same time.
             */
            $log = OrderNotificationLog::query()
                ->where('order_id', $order->id)
                ->where('notification_type', $type)
                ->where('recipient_email', $recipient)
                ->first();

            if (!$log) {
                throw $exception;
            }
        }

        if ($log->sent_at !== null) {
            return;
        }

        try {
            $callback();

            $log->forceFill([
                'sent_at' => now(),
                'failure_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            $log->forceFill([
                'failure_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    65000
                ),
            ])->save();

            Log::error(
                'Order notification could not be queued.',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'notification_type' => $type,
                    'recipient' => $recipient,
                    'exception' => $exception,
                ]
            );

            report($exception);
        }
    }

    private function customerEmail(Order $order): ?string
    {
        $possibleEmails = [
            $order->email ?? null,
            $order->customer_email ?? null,
            $order->billing_email ?? null,
            $order->shipping_email ?? null,
            $order->user?->email,
        ];

        foreach ($possibleEmails as $email) {
            if (
                is_string($email)
                && filter_var(
                    trim($email),
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                return strtolower(trim($email));
            }
        }

        return null;
    }
}