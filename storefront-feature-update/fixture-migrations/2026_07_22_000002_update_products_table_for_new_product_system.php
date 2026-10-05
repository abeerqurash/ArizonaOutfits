<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Products table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->string('slug')->unique();
                $table->string('sku')->nullable()->unique();

                $table->text('short_description')->nullable();
                $table->longText('long_description')->nullable();
                $table->longText('additional_info')->nullable();

                $table->decimal('regular_price', 12, 2)->default(0);
                $table->decimal('sale_price', 12, 2)->nullable();

                $table->unsignedInteger('stock')->default(0);

                $table->string('status')->default('draft');

                $table->string('featured_image')->nullable();

                $table->unsignedBigInteger('views_count')->default(0);
                $table->unsignedBigInteger('favorites_count')->default(0);
                $table->unsignedBigInteger('cart_count')->default(0);
                $table->unsignedBigInteger('purchase_count')->default(0);

                $table->decimal('average_rating', 3, 2)->default(0);

                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();

                $table->timestamps();
            });
        } else {
            $this->addMissingProductColumns();
        }

        /*
        |--------------------------------------------------------------------------
        | Product category pivot table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_category_product')) {
            Schema::create(
                'product_category_product',
                function (Blueprint $table) {
                    $table->id();

                    $table->foreignId('product_id')
                        ->constrained('products')
                        ->cascadeOnDelete();

                    $table->foreignId('product_category_id')
                        ->constrained('product_categories')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique([
                        'product_id',
                        'product_category_id',
                    ], 'product_category_unique');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product tags pivot table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_product_tag')) {
            Schema::create(
                'product_product_tag',
                function (Blueprint $table) {
                    $table->id();

                    $table->foreignId('product_id')
                        ->constrained('products')
                        ->cascadeOnDelete();

                    $table->foreignId('product_tag_id')
                        ->constrained('product_tags')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique([
                        'product_id',
                        'product_tag_id',
                    ], 'product_tag_unique');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product options pivot table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_option_product')) {
            Schema::create(
                'product_option_product',
                function (Blueprint $table) {
                    $table->id();

                    $table->foreignId('product_id')
                        ->constrained('products')
                        ->cascadeOnDelete();

                    $table->foreignId('product_option_id')
                        ->constrained('product_options')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique([
                        'product_id',
                        'product_option_id',
                    ], 'product_option_unique');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product option values pivot table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_option_value_product')) {
            Schema::create(
                'product_option_value_product',
                function (Blueprint $table) {
                    $table->id();

                    $table->foreignId('product_id')
                        ->constrained('products')
                        ->cascadeOnDelete();

                    $table->foreignId('product_option_value_id')
                        ->constrained('product_option_values')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique([
                        'product_id',
                        'product_option_value_id',
                    ], 'product_option_value_unique');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product images table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->cascadeOnDelete();

                $table->string('image');
                $table->string('alt_text')->nullable();

                $table->unsignedInteger('sort_order')->default(0);

                $table->boolean('is_primary')->default(false);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Product variants table
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->cascadeOnDelete();

                $table->string('title')->nullable();
                $table->string('sku')->nullable()->unique();

                $table->decimal('regular_price', 12, 2)->nullable();
                $table->decimal('sale_price', 12, 2)->nullable();

                $table->unsignedInteger('stock')->default(0);

                $table->json('options')->nullable();

                $table->string('image')->nullable();

                $table->string('status')->default('active');

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Fix existing order_items options
        |--------------------------------------------------------------------------
        |
        | Existing values such as "[]" are double-encoded JSON.
        | Convert them to valid JSON arrays: []
        |
        */

        if (
            Schema::hasTable('order_items')
            && Schema::hasColumn('order_items', 'options')
        ) {
            DB::table('order_items')
                ->where('options', '"[]"')
                ->update([
                    'options' => '[]',
                ]);

            DB::table('order_items')
                ->whereNull('options')
                ->update([
                    'options' => '[]',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fill missing product slugs
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('products')
            && Schema::hasColumn('products', 'slug')
        ) {
            $products = DB::table('products')
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->get([
                    'id',
                    'title',
                ]);

            foreach ($products as $product) {
                $baseSlug = str($product->title ?: 'product')
                    ->slug()
                    ->toString();

                if ($baseSlug === '') {
                    $baseSlug = 'product';
                }

                $slug = $baseSlug;
                $counter = 1;

                while (
                    DB::table('products')
                        ->where('slug', $slug)
                        ->where('id', '!=', $product->id)
                        ->exists()
                ) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'slug' => $slug,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize existing product values
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'regular_price')) {
                DB::table('products')
                    ->whereNull('regular_price')
                    ->update([
                        'regular_price' => 0,
                    ]);
            }

            if (Schema::hasColumn('products', 'stock')) {
                DB::table('products')
                    ->whereNull('stock')
                    ->update([
                        'stock' => 0,
                    ]);
            }

            if (Schema::hasColumn('products', 'status')) {
                DB::table('products')
                    ->whereNull('status')
                    ->orWhere('status', '')
                    ->update([
                        'status' => 'draft',
                    ]);
            }

            $counterColumns = [
                'views_count',
                'favorites_count',
                'cart_count',
                'purchase_count',
            ];

            foreach ($counterColumns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    DB::table('products')
                        ->whereNull($column)
                        ->update([
                            $column => 0,
                        ]);
                }
            }

            if (Schema::hasColumn('products', 'average_rating')) {
                DB::table('products')
                    ->whereNull('average_rating')
                    ->update([
                        'average_rating' => 0,
                    ]);
            }
        }
    }

    private function addMissingProductColumns(): void
    {
        if (!Schema::hasColumn('products', 'title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('title')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('products', 'sku')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('sku')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('products', 'short_description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('short_description')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'long_description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->longText('long_description')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'additional_info')) {
            Schema::table('products', function (Blueprint $table) {
                $table->longText('additional_info')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'regular_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('regular_price', 12, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'sale_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('sale_price', 12, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('stock')->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('status')->default('draft');
            });
        }

        if (!Schema::hasColumn('products', 'featured_image')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('featured_image')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'views_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('views_count')->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'favorites_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('favorites_count')->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'cart_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('cart_count')->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'purchase_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('purchase_count')->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'average_rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('average_rating', 3, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('products', 'meta_title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('meta_title')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'meta_description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('meta_description')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'meta_keywords')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('meta_keywords')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'created_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'updated_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Do not remove product columns automatically
        |--------------------------------------------------------------------------
        |
        | Removing these columns could destroy existing product data.
        | Only newly created supporting tables are removed.
        |
        */

        Schema::dropIfExists('product_option_value_product');
        Schema::dropIfExists('product_option_product');
        Schema::dropIfExists('product_product_tag');
        Schema::dropIfExists('product_category_product');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
    }
};