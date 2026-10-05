from pathlib import Path
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'administration-audit';T=O/'_checks';T.mkdir(exist_ok=True)
s=(R/'customer-content-audit/_checks/live_schema.php').read_text(encoding='utf-8-sig')
a=s.index('foreach ([');b=s.index(' as $table)',a)
s=s[:a]+"foreach (['admins','admin_roles','admin_permissions','admin_permission_role','admin_role_admin','ecommerce_settings','pages','navigation_menus','navigation_menu_items','notifications','admin_audit_logs','admin_backups','email_templates']"+s[b:]
(T/'live_schema.php').write_text(s,encoding='utf-8')
bootstrap='''<?php
require __DIR__.'/../../customer-content-audit/_checks/bootstrap.php';
use Illuminate\\Support\\Facades\\{Schema,DB};
use Illuminate\\Database\\Schema\\Blueprint;
Schema::table('admins',function(Blueprint $t){foreach(['phone','password','profile_image','author_title'] as $f)$t->string($f)->nullable();$t->dateTime('email_verified_at')->nullable();$t->unsignedBigInteger('legacy_user_id')->nullable();$t->rememberToken();});
Schema::table('orders',fn(Blueprint $t)=>$t->string('shipping_country')->nullable());
foreach(['2026_07_22_000001_prepare_ecommerce_admin_tables.php','2026_09_18_220000_add_bank_transfer_settings_to_ecommerce_settings_table.php','2026_02_23_233358_create_pages_table.php','2026_08_09_000012_upgrade_pages_table_for_cms.php','2026_08_09_000013_add_custom_blade_support_to_pages.php','2026_08_09_000014_create_navigation_menus_tables.php','2026_08_07_000006_create_admin_roles_and_permissions_tables.php','2026_09_24_000005_create_admin_role_admin_table.php','2026_08_09_000008_create_notifications_table.php','2026_08_09_000007_create_admin_audit_logs_table.php','2026_08_09_000009_create_admin_backups_table.php','2026_08_09_000010_create_email_templates_table.php'] as $name)(require __DIR__.'/../../database/migrations/'.$name)->up();
Schema::table('pages',function(Blueprint $t){$t->foreignId('creator_admin_id')->nullable()->constrained('admins')->nullOnDelete();$t->foreignId('editor_admin_id')->nullable()->constrained('admins')->nullOnDelete();});
foreach(['admin_audit_logs','admin_backups','email_templates'] as $name)Schema::table($name,fn(Blueprint $t)=>$t->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete());
config(['filesystems.disks.local'=>['driver'=>'local','root'=>__DIR__.'/files/private','throw'=>true],'filesystems.disks.public'=>['driver'=>'local','root'=>__DIR__.'/files/public','throw'=>true],'view.compiled'=>__DIR__.'/compiled']);
@mkdir(__DIR__.'/compiled',0777,true);@mkdir(__DIR__.'/files/private',0777,true);@mkdir(__DIR__.'/files/public',0777,true);
$admin=App\\Models\\Admin::find(99);AuthFix();
function AuthFix(){global $admin;Illuminate\\Support\\Facades\\Auth::guard('admin')->setUser($admin);Illuminate\\Support\\Facades\\Auth::shouldUse('admin');}
'''
(T/'bootstrap.php').write_text(bootstrap,encoding='utf-8')
s=(R/'inventory-purchasing-audit/_checks/routes.php').read_text(encoding='utf-8-sig');a=s.index('require __DIR__');b=s.index('$routes=',a);s=s[:a]+"require __DIR__.'/bootstrap.php';\n"+s[b:]
import re
s=re.sub(r'\$prefixes=\[[^\]]*\]',"$prefixes=['admin.settings.','admin.pages.','admin.navigation-menus.','admin.analytics.','admin.admin-users.','admin.admin-roles.','admin.notifications.','admin.audit-logs.','admin.backups.','admin.email-templates.','pages.']",s)
s=re.sub(r'\$directories=\[[^\]]*\]',"$directories=['admin/settings','admin/pages','admin/navigation-menus','admin/analytics','admin/admin-users','admin/admin-roles','admin/notifications','admin/audit-logs','admin/backups','admin/email-templates']",s)
(T/'routes.php').write_text(s,encoding='utf-8')
print('Prepared isolated fixture bootstrap, route inspector and read-only schema probe.')
