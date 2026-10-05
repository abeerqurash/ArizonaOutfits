<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('order_number')->unique();

                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone')->nullable();

                $table->string('billing_address')->nullable();
                $table->string('shipping_address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country')->nullable();

                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('shipping', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);

                $table->string('payment_method')->nullable();
                $table->string('payment_status')->default('pending');
                $table->string('order_status')->default('pending');

                $table->string('tracking_number')->nullable();
                $table->text('admin_notes')->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'order_number')) {
                    $table->string('order_number')->nullable()->unique();
                }

                if (!Schema::hasColumn('orders', 'customer_name')) {
                    $table->string('customer_name')->nullable();
                }

                if (!Schema::hasColumn('orders', 'customer_email')) {
                    $table->string('customer_email')->nullable();
                }

                if (!Schema::hasColumn('orders', 'customer_phone')) {
                    $table->string('customer_phone')->nullable();
                }

                if (!Schema::hasColumn('orders', 'billing_address')) {
                    $table->string('billing_address')->nullable();
                }

                if (!Schema::hasColumn('orders', 'shipping_address')) {
                    $table->string('shipping_address')->nullable();
                }

                if (!Schema::hasColumn('orders', 'city')) {
                    $table->string('city')->nullable();
                }

                if (!Schema::hasColumn('orders', 'state')) {
                    $table->string('state')->nullable();
                }

                if (!Schema::hasColumn('orders', 'postal_code')) {
                    $table->string('postal_code')->nullable();
                }

                if (!Schema::hasColumn('orders', 'country')) {
                    $table->string('country')->nullable();
                }

                if (!Schema::hasColumn('orders', 'subtotal')) {
                    $table->decimal('subtotal', 12, 2)->default(0);
                }

                if (!Schema::hasColumn('orders', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0);
                }

                if (!Schema::hasColumn('orders', 'shipping')) {
                    $table->decimal('shipping', 12, 2)->default(0);
                }

                if (!Schema::hasColumn('orders', 'tax')) {
                    $table->decimal('tax', 12, 2)->default(0);
                }

                if (!Schema::hasColumn('orders', 'total')) {
                    $table->decimal('total', 12, 2)->default(0);
                }

                if (!Schema::hasColumn('orders', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }

                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending');
                }

                if (!Schema::hasColumn('orders', 'order_status')) {
                    $table->string('order_status')->default('pending');
                }

                if (!Schema::hasColumn('orders', 'tracking_number')) {
                    $table->string('tracking_number')->nullable();
                }

                if (!Schema::hasColumn('orders', 'admin_notes')) {
                    $table->text('admin_notes')->nullable();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Order items
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('variant_id')
                    ->nullable();

                $table->string('product_name');
                $table->string('sku')->nullable();

                $table->unsignedInteger('quantity')->default(1);

                $table->decimal('price', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);

                $table->json('options')->nullable();

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active');
            }

            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('name')->nullable();
                $table->string('email')->nullable();

                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('title')->nullable();
                $table->text('review');

                $table->string('status')->default('pending');

                $table->timestamps();
            });
        } else {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'product_id')) {
                    $table->foreignId('product_id')
                        ->nullable()
                        ->constrained()
                        ->cascadeOnDelete();
                }

                if (!Schema::hasColumn('reviews', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->constrained()
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('reviews', 'name')) {
                    $table->string('name')->nullable();
                }

                if (!Schema::hasColumn('reviews', 'email')) {
                    $table->string('email')->nullable();
                }

                if (!Schema::hasColumn('reviews', 'rating')) {
                    $table->unsignedTinyInteger('rating')->default(5);
                }

                if (!Schema::hasColumn('reviews', 'title')) {
                    $table->string('title')->nullable();
                }

                if (!Schema::hasColumn('reviews', 'review')) {
                    $table->text('review')->nullable();
                }

                if (!Schema::hasColumn('reviews', 'status')) {
                    $table->string('status')->default('pending');
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | E-commerce settings
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('ecommerce_settings')) {
            Schema::create('ecommerce_settings', function (Blueprint $table) {
                $table->id();

                $table->string('store_name')->default('IdeoStream');
                $table->string('store_email')->nullable();
                $table->string('store_phone')->nullable();
                $table->text('store_address')->nullable();

                $table->string('currency')->default('USD');
                $table->string('currency_symbol')->default('$');

                $table->decimal('tax_percentage', 8, 2)->default(0);
                $table->decimal('shipping_fee', 12, 2)->default(0);
                $table->decimal('free_shipping_threshold', 12, 2)->nullable();

                $table->string('order_prefix')->default('ORD');

                $table->boolean('guest_checkout_enabled')->default(true);
                $table->boolean('cash_on_delivery_enabled')->default(true);
                $table->boolean('stock_management_enabled')->default(true);
                $table->boolean('maintenance_mode')->default(false);

                $table->unsignedInteger('low_stock_threshold')->default(5);

                $table->text('checkout_notice')->nullable();
                $table->text('order_email_message')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_settings');
    }
};