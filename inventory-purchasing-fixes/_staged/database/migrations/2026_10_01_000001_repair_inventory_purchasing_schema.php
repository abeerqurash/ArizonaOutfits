<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Historical placeholder migrations did not create these tables on fresh installations.
        if (!Schema::hasTable('supplier_documents')) {
            Schema::create('supplier_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->string('document_type')->nullable(); $table->string('title');
                $table->string('file_name'); $table->string('file_path');
                $table->string('mime_type')->nullable(); $table->unsignedBigInteger('file_size')->nullable();
                $table->date('expiry_date')->nullable(); $table->text('notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('supplier_ratings')) {
            Schema::create('supplier_ratings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                foreach (['quality_rating', 'pricing_rating', 'delivery_rating', 'communication_rating', 'reliability_rating'] as $column) $table->unsignedTinyInteger($column)->nullable();
                $table->decimal('overall_rating', 3, 2)->nullable(); $table->text('review')->nullable();
                $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        foreach (['title', 'would_recommend', 'rated_at'] as $column) {
            if (!Schema::hasColumn('supplier_ratings', $column)) {
                Schema::table('supplier_ratings', function (Blueprint $table) use ($column): void {
                    match ($column) {
                        'title' => $table->string('title')->nullable(),
                        'would_recommend' => $table->boolean('would_recommend')->nullable(),
                        'rated_at' => $table->timestamp('rated_at')->nullable(),
                    };
                });
            }
        }
        foreach ([
            'suppliers' => ['created_by_admin_id'],
            'purchase_order_receipts' => ['received_by_admin_id'],
            'supplier_returns' => ['created_by_admin_id', 'completed_by_admin_id', 'cancelled_by_admin_id'],
            'supplier_documents' => ['uploaded_by_admin_id'],
            'supplier_ratings' => ['rated_by_admin_id'],
            'supplier_purchase_order_deliveries' => ['sent_by_admin_id']
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;
            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    Schema::table($tableName, function (Blueprint $table) use ($column): void {
                        $table->foreignId($column)->nullable()->constrained('admins')->nullOnDelete();
                    });
                }
            }
        }
        if (!Schema::hasColumn('purchase_orders', 'lock_version')) {
            Schema::table('purchase_orders', fn (Blueprint $table) => $table->unsignedBigInteger('lock_version')->default(0));
        }
        // Existing user IDs remain unchanged. Equal numeric admin IDs are never inferred.
    }
    public function down(): void
    {
        // Intentionally preserve additive schema and historical attribution on rollback.
        // Restore a database backup if a complete pre-installation rollback is needed.
    }
};
