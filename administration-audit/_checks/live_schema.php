<?php
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$result=[];
try {
    foreach (['admins','admin_roles','admin_permissions','admin_permission_role','admin_role_admin','ecommerce_settings','pages','navigation_menus','navigation_menu_items','notifications','admin_audit_logs','admin_backups','email_templates'] as $table) {
        $result[$table]=['columns'=>Illuminate\Support\Facades\Schema::getColumnListing($table),'foreign_keys'=>Illuminate\Support\Facades\Schema::getForeignKeys($table)];
    }
    file_put_contents(__DIR__.'/../live-schema.json',json_encode($result,JSON_PRETTY_PRINT));
    foreach ($result as $table=>$data) echo $table.': '.implode(', ',$data['columns'])."\n";
} catch (Throwable $e) {
    echo 'Local database metadata read unavailable: '.get_class($e)."\n";
}
