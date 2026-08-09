<?php

use Illuminate\Database\Migrations\Migration;

/*
|--------------------------------------------------------------------------
| Historical placeholder
|--------------------------------------------------------------------------
|
| This migration file was accidentally overwritten with the
| App\Models\SupplierDocument model. The actual supplier_documents table
| migration is 2026_08_07_000001_create_supplier_documents_table.php.
|
| Keep this timestamp as a no-op migration so existing migration history is
| not renamed or deleted and fresh databases can continue to the real table
| migration safely.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally empty. See the migration referenced above.
    }

    public function down(): void
    {
        // Intentionally empty. The real migration owns the table rollback.
    }
};
