<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_email_identities', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('source', 20);
            $table->string('email')->nullable();
            $table->string('normalized_email')->nullable();
            $table->string('provider_user_id')->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->timestamp('verification_pending_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();

            /*
             * Only the custom source can become a local email/password login.
             * Google/Facebook remain independent OAuth credentials even when
             * they return the same email string.
             */
            $table->boolean('is_login_enabled')->default(false);

            $table->timestamps();

            /*
             * Exactly one row per source for each customer. Disconnecting a
             * source keeps the row for security/history and reconnect updates it.
             */
            $table->unique(
                ['user_id', 'source'],
                'customer_email_identity_user_source_unique'
            );

            /*
             * A provider account ID may belong to only one local customer.
             * NULL remains allowed for custom identities and providers that
             * have not supplied an ID yet.
             */
            $table->unique(
                ['source', 'provider_user_id'],
                'customer_email_identity_provider_unique'
            );

            $table->index(
                'normalized_email',
                'customer_email_identity_email_index'
            );

            $table->index(
                ['user_id', 'disconnected_at'],
                'customer_email_identity_connected_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Safe legacy backfill
        |--------------------------------------------------------------------------
        |
        | This migration intentionally backfills only states we can prove from
        | existing columns. It never guesses a Google/Facebook email for a
        | provider that was linked later to a phone/custom-email account.
        |
        | Those provider emails will be captured on the next successful OAuth
        | connect/login callback in the following implementation step.
        |
        */

        $users = DB::table('users')
            ->select([
                'id',
                'email',
                'email_verified_at',
                'email_login_enabled_at',
                'email_login_pending_at',
                'google_id',
                'facebook_id',
                'registration_method',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            $email = $this->normalizeEmail($user->email);
            $now = $user->updated_at ?? $user->created_at ?? now();

            /*
             * Explicit custom-email state created by DQ60ZK.
             */
            if (
                $email !== null
                && (
                    $user->email_login_enabled_at !== null
                    || $user->email_login_pending_at !== null
                    || ! in_array(
                        $user->registration_method,
                        ['google', 'facebook'],
                        true
                    )
                )
            ) {
                DB::table('customer_email_identities')->insert([
                    'user_id' => $user->id,
                    'source' => 'custom',
                    'email' => $email,
                    'normalized_email' => $email,
                    'provider_user_id' => null,
                    'verified_at' =>
                        $user->email_login_enabled_at !== null
                            ? ($user->email_verified_at ?? $user->email_login_enabled_at)
                            : null,
                    'verification_pending_at' =>
                        $user->email_login_enabled_at === null
                            ? $user->email_login_pending_at
                            : null,
                    'connected_at' =>
                        $user->email_login_enabled_at
                        ?? $user->email_login_pending_at
                        ?? $user->created_at
                        ?? now(),
                    'disconnected_at' => null,
                    'is_login_enabled' =>
                        $user->email_login_enabled_at !== null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /*
             * For a legacy account originally created with Google, users.email
             * is known to have come from that OAuth callback. For later-linked
             * Google accounts we do not guess that users.email belongs to Google.
             */
            if ($user->google_id !== null) {
                $googleEmail =
                    $user->registration_method === 'google'
                        ? $email
                        : null;

                DB::table('customer_email_identities')->insert([
                    'user_id' => $user->id,
                    'source' => 'google',
                    'email' => $googleEmail,
                    'normalized_email' => $googleEmail,
                    'provider_user_id' => (string) $user->google_id,
                    'verified_at' => $user->created_at ?? now(),
                    'verification_pending_at' => null,
                    'connected_at' => $user->created_at ?? now(),
                    'disconnected_at' => null,
                    'is_login_enabled' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /*
             * Same conservative rule for legacy Facebook-created accounts.
             */
            if ($user->facebook_id !== null) {
                $facebookEmail =
                    $user->registration_method === 'facebook'
                        ? $email
                        : null;

                DB::table('customer_email_identities')->insert([
                    'user_id' => $user->id,
                    'source' => 'facebook',
                    'email' => $facebookEmail,
                    'normalized_email' => $facebookEmail,
                    'provider_user_id' => (string) $user->facebook_id,
                    'verified_at' => $user->created_at ?? now(),
                    'verification_pending_at' => null,
                    'connected_at' => $user->created_at ?? now(),
                    'disconnected_at' => null,
                    'is_login_enabled' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_email_identities');
    }

    private function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized !== '' ? $normalized : null;
    }
};
