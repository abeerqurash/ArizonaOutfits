from pathlib import Path
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'customer-content-audit';T=O/'_checks';T.mkdir(exist_ok=True)
s=(R/'inventory-purchasing-audit/_checks/bootstrap.php').read_text(encoding='utf-8-sig')
s=s.replace("require __DIR__.'/../../vendor/autoload.php';",'''require __DIR__.'/../../vendor/autoload.php';
if (getenv('CUSTOMER_CONTENT_STAGE')) spl_autoload_register(function ($class) {
    if (str_starts_with($class,'App\\\\')) {
        $path=__DIR__.'/../../customer-content-fixes/_staged/app/'.str_replace('\\\\','/',substr($class,4)).'.php';
        if (is_file($path)) require $path;
    }
},true,true);
''')
s=s.replace("$customer=User::create(",'''Schema::table('users',function(Blueprint $t){$t->boolean('is_admin')->default(false);$t->boolean('is_super_admin')->default(false);$t->string('status')->default('active');foreach(['phone','avatar','registration_method','google_id','facebook_id','pending_email'] as $f)$t->string($f)->nullable();foreach(['email_verified_at','phone_verified_at','password_set_at','email_login_enabled_at','email_login_pending_at','pending_email_requested_at','security_reminder_shown_at'] as $f)$t->dateTime($f)->nullable();});
Schema::table('admins',function(Blueprint $t){$t->string('status')->default('active');$t->boolean('is_super_admin')->default(true);});
foreach (['2026_03_07_131000_create_categories_table.php','2026_03_07_131100_create_posts_table.php','2026_03_07_131207_create_category_post_table.php','2026_09_28_000001_upgrade_posts_for_publishing.php','2026_10_01_000001_create_post_redirects_table.php','2026_10_01_000002_create_post_revisions_table.php'] as $file){$migration=require __DIR__.'/../../database/migrations/'.$file;if(!is_object($migration))$migration=new CreateCategoryPostTable;$migration->up();}
Schema::table('categories',fn(Blueprint $t)=>$t->string('image')->nullable());
Schema::create('reviews',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();foreach(['name','email','title'] as $f)$t->string($f)->nullable();$t->integer('rating');$t->text('review');$t->string('status')->default('pending');$t->timestamps();});
if (getenv('CUSTOMER_CONTENT_STAGE')) $app['view']->getFinder()->prependLocation(__DIR__.'/../../customer-content-fixes/_staged/resources/views');
$customer=User::create(''')
s=s.replace("$t->string('status')->default('active');foreach", "$t->string('status')->default('active');$t->rememberToken();foreach")
s=s.replace("$customer=User::create(", "Schema::create('favorites',function(Blueprint $t){$t->id();$t->integer('user_id');$t->integer('product_id');$t->timestamps();});\n$customer=User::create(")
(T/'bootstrap.php').write_text(s,encoding='utf-8')
s=(R/'inventory-purchasing-audit/_checks/live_schema.php').read_text(encoding='utf-8-sig')
a=s.index("foreach ([");b=s.index(" as $table)",a)
s=s[:a]+"foreach (['users','reviews','categories','posts','category_post','post_revisions','post_redirects']"+s[b:]
(T/'live_schema.php').write_text(s,encoding='utf-8')
print('Prepared isolated audit and read-only schema probe.')
