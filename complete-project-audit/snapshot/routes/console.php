<?php

use App\Models\Post;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
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


Artisan::command('posts:publish-scheduled {--dry-run : Preview due articles without changing them}', function (): int {
    $cutoff = now();
    $dryRun = (bool) $this->option('dry-run');
    $published = 0;
    $ready = 0;
    $skipped = 0;
    $this->info('Checking scheduled articles at ' . $cutoff->format('Y-m-d H:i:s T') . '.');

    // Capture due IDs in small batches. Recheck each article under a row lock
    // so a concurrent admin update or publisher cannot be overwritten.
    Post::query()->scheduled()->where('scheduled_at', '<=', $cutoff)
        ->select('id')->chunkById(100, function ($posts) use ($cutoff, $dryRun, &$published, &$ready, &$skipped): void {
            foreach ($posts as $candidate) {
                $result = DB::transaction(function () use ($candidate, $cutoff, $dryRun): array {
                    $post = Post::query()->whereKey($candidate->id)
                        ->scheduled()->where('scheduled_at', '<=', $cutoff)
                        ->lockForUpdate()->first();
                    if (!$post) {
                        return ['state' => 'changed'];
                    }

                    // Public articles are manually authored Blade templates.
                    // Keep an article scheduled if its public body is missing.
                    $template = 'blogs.posts.' . ($post->template ?: $post->slug);
                    if (!View::exists($template)) {
                        return ['state' => 'missing', 'id' => $post->id, 'template' => $template];
                    }
                    if ($dryRun) {
                        return ['state' => 'ready', 'id' => $post->id];
                    }

                    // Use the intended publication time even after downtime.
                    $post->forceFill([
                        'status' => Post::STATUS_PUBLISHED,
                        'published_at' => $post->scheduled_at,
                        'scheduled_at' => null,
                    ])->save();
                    return ['state' => 'published', 'id' => $post->id];
                }, 3);

                if ($result['state'] === 'missing') {
                    ++$skipped;
                    $this->warn('Skipped article #' . $result['id'] . ': missing template ' . $result['template'] . '.');
                } elseif ($result['state'] === 'ready') {
                    ++$ready;
                    $this->line('Would publish article #' . $result['id'] . '.');
                } elseif ($result['state'] === 'published') {
                    ++$published;
                    $this->line('Published article #' . $result['id'] . '.');
                }
            }
        });

    $this->info($dryRun
        ? 'Dry run: ' . $ready . ' ready; ' . $skipped . ' skipped. No articles were changed.'
        : 'Published: ' . $published . '; skipped: ' . $skipped . '.');

    // A missing article body needs attention instead of a silent success.
    return $skipped > 0 ? 1 : 0;
})->purpose('Publish due scheduled blog articles that have a public Blade template');

Schedule::command('posts:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping(10);
