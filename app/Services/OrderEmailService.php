<?php

namespace App\Services;

use App\Mail\AdminNewOrderMail;
use App\Mail\CustomerBankTransferRejectedMail;
use App\Mail\CustomerBankTransferVerifiedMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderNotificationLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderEmailService
{
    public const CUSTOMER_CONFIRMATION = 'customer_order_confirmation';
    public const ADMIN_NEW_ORDER = 'admin_new_order';
    public const BANK_TRANSFER_VERIFIED = 'bank_transfer_verified';
    public const BANK_TRANSFER_REJECTED = 'bank_transfer_rejected';

    public function sendOrderEmails(Order $order): void
    {
        $this->sendCustomerConfirmation($order);
        $this->sendAdminNotification($order);
    }

    public function sendCustomerConfirmation(Order $order): void
    {
        $email = $this->customerEmail($order);

        if (!$email) {
            Log::warning(
                'Customer order email was not sent because the order has no customer email address.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::CUSTOMER_CONFIRMATION,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(new CustomerOrderConfirmationMail($order));
            }
        );
    }

    public function sendAdminNotification(Order $order): void
    {
        $email = config('mail.admin_order_email');

        if (!$email) {
            Log::warning(
                'Admin order email was not sent because ADMIN_ORDER_EMAIL is not configured.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::ADMIN_NEW_ORDER,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(new AdminNewOrderMail($order));
            }
        );
    }

    public function sendBankTransferVerified(Order $order): void
    {
        $email = $this->customerEmail($order);

        if (!$email) {
            Log::warning(
                'Bank-transfer verification email was not sent because the order has no customer email address.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::BANK_TRANSFER_VERIFIED,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(new CustomerBankTransferVerifiedMail($order));
            }
        );
    }

    public function sendBankTransferRejected(Order $order): void
    {
        $email = $this->customerEmail($order);

        if (!$email) {
            Log::warning(
                'Bank-transfer rejection email was not sent because the order has no customer email address.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
            return;
        }

        $this->sendOnce(
            order: $order,
            type: self::BANK_TRANSFER_REJECTED,
            recipient: $email,
            callback: function () use ($email, $order): void {
                Mail::to($email)->queue(new CustomerBankTransferRejectedMail($order));
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
            DB::transaction(function () use (
                $order,
                $type,
                $recipient,
                $callback
            ): void {
                /*
                 * The database unique key identifies one logical
                 * notification. The row lock serializes concurrent
                 * webhook/request workers before they can queue it.
                 */
                $log = OrderNotificationLog::query()
                    ->where('order_id', $order->id)
                    ->where('notification_type', $type)
                    ->where('recipient_email', $recipient)
                    ->lockForUpdate()
                    ->first();

                if (!$log) {
                    try {
                        $log = OrderNotificationLog::query()->create([
                            'order_id' => $order->id,
                            'notification_type' => $type,
                            'recipient_email' => $recipient,
                            'sent_at' => null,
                            'failure_message' => null,
                        ]);
                    } catch (QueryException $exception) {
                        /*
                         * A competing transaction may have inserted the
                         * unique row first. Lock and reuse that row.
                         */
                        $log = OrderNotificationLog::query()
                            ->where('order_id', $order->id)
                            ->where('notification_type', $type)
                            ->where('recipient_email', $recipient)
                            ->lockForUpdate()
                            ->first();

                        if (!$log) {
                            throw $exception;
                        }
                    }
                }

                if ($log->sent_at !== null) {
                    return;
                }

                try {
                    $callback();

                    /*
                     * Here sent_at means successfully handed to Laravel's
                     * configured mail queue, not guaranteed final delivery.
                     */
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
            }, 3);
        } catch (Throwable $exception) {
            Log::error(
                'Order notification idempotency transaction failed.',
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
                && filter_var(trim($email), FILTER_VALIDATE_EMAIL)
            ) {
                return strtolower(trim($email));
            }
        }

        return null;
    }
}
