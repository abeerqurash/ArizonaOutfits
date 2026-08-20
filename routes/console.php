<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email}', function (string $email): int {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Enter a valid recipient email address.');

        return 1;
    }

    Mail::raw(
        'Arizona Outfits email configuration is working.',
        function ($message) use ($email): void {
            $message
                ->to($email)
                ->subject('Arizona Outfits email test');
        }
    );

    $this->info('Test email sent to ' . $email . '.');

    return 0;
})->purpose('Send a test email using the configured Laravel mailer');

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command(
    'queue:prune-batches --hours=168 --unfinished=168 --cancelled=168'
)
    ->dailyAt('02:10')
    ->withoutOverlapping();
