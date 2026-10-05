from pathlib import Path
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'inventory-purchasing-audit';T=O/'_checks'
s=(R/'commerce-menu-audit/_checks/verify.php').read_text(encoding='utf-8-sig')
s=s[:s.index('$customer=')]
s=s.replace("'session.driver'=>'array'", "'logging.default'=>'inventory_audit','logging.channels.inventory_audit'=>['driver'=>'single','path'=>__DIR__.'/../diagnostic.log'],'session.driver'=>'array'")
s+='''
foreach ([
'2026_07_28_205829_create_inventory_alerts_table.php',
'2026_08_04_181151_create_suppliers_table.php',
'2026_08_01_032443_create_purchase_orders_table.php',
'2026_08_01_032609_create_purchase_order_items_table.php',
'2026_08_04_181154_add_supplier_id_to_purchase_orders_table.php',
'2026_08_04_181152_create_supplier_contacts_table.php',
'2026_08_07_000003_create_supplier_purchase_order_deliveries_table.php',
'2026_08_07_000004_create_supplier_products_table.php',
'2026_08_07_000005_create_receipts_and_supplier_returns_tables.php',
] as $migration)(require __DIR__.'/../../database/migrations/'.$migration)->up();
Schema::table('purchase_orders',function(Blueprint $t){$t->foreignId('admin_id')->nullable()->constrained('admins');});
Schema::table('product_variants',function(Blueprint $t){$t->integer('reorder_quantity')->nullable();});
// These two tables mirror the read-only local schema metadata, not the
// missing replacement migrations named by the historical placeholders.
Schema::create('supplier_ratings',function(Blueprint $t){$t->id();$t->foreignId('supplier_id')->constrained();$t->foreignId('purchase_order_id')->nullable()->constrained();foreach(['quality_rating','pricing_rating','delivery_rating','communication_rating','reliability_rating'] as $f)$t->integer($f)->nullable();$t->decimal('overall_rating',3,2)->nullable();$t->text('review')->nullable();$t->foreignId('rated_by')->nullable()->constrained('users');$t->timestamps();});
Schema::create('supplier_documents',function(Blueprint $t){$t->id();$t->foreignId('supplier_id')->constrained();$t->string('document_type')->nullable();$t->string('title');$t->string('file_name');$t->string('file_path');$t->string('mime_type')->nullable();$t->integer('file_size')->nullable();$t->date('expiry_date')->nullable();$t->text('notes')->nullable();$t->foreignId('uploaded_by')->nullable()->constrained('users');$t->timestamps();});
$customer=User::create(['name'=>'Fixture customer','email'=>'customer@example.test']);$other=User::create(['name'=>'Other customer','email'=>'other@example.test']);
DB::table('admins')->insert(['id'=>99,'name'=>'Fixture admin','email'=>'admin@example.test']);$admin=(new Admin)->forceFill(['id'=>99,'name'=>'Fixture admin','status'=>'active','is_super_admin'=>true]);
$app['auth']->guard('admin')->setUser($admin);$app['auth']->guard('web')->setUser($customer);$app['auth']->shouldUse('admin');$app['session']->start();
config(['mail.default'=>'array','inventory.alert_email'=>'audit@example.test','inventory.low_stock_threshold'=>5,'view.compiled'=>__DIR__.'/compiled','filesystems.disks.public'=>['driver'=>'local','root'=>__DIR__.'/files','throw'=>true]]);@mkdir(__DIR__.'/compiled');$app['view']->share('errors',new Illuminate\\Support\\ViewErrorBag);Illuminate\\Support\\Facades\\Mail::fake();Illuminate\\Support\\Facades\\Bus::fake();
function req($data=[]){global $app,$admin;$r=Illuminate\\Http\\Request::create('http://arizona.test/admin/audit','POST',$data);$r->setUserResolver(fn()=>$admin);$r->setLaravelSession($app['session']->driver());$app->instance('request',$r);return $r;}
$results=[];
function check($label,$ok,$detail=''){global $results;$results[]=['result'=>$ok?'PASS':'ISSUE','check'=>$label,'detail'=>$detail];echo ($ok?'PASS: ':'ISSUE: ').$label.($detail?' -- '.$detail:'')."\\n";}
function run($label,$callback){try{$callback();}catch(Throwable $e){global $results;$results[]=['result'=>'ERROR','check'=>$label,'exception'=>get_class($e),'detail'=>$e->getMessage()];echo 'ERROR: '.$label.' '.get_class($e).' '.$e->getMessage()."\\n";}}
function validationBlocked($callback){try{$callback();return false;}catch(Illuminate\\Validation\\ValidationException){return true;}}
function supplier($extra=[]){static $i=0;return App\\Models\\Supplier::create(array_replace(['company_name'=>'Supplier '.(++$i),'email'=>'supplier'.$i.'@example.test','currency'=>'GBP','status'=>'active'],$extra));}
function po($supplier,$extra=[]){return App\\Models\\PurchaseOrder::create(array_replace(['supplier_id'=>$supplier->id,'supplier_name'=>$supplier->company_name,'status'=>'ordered','currency'=>$supplier->currency,'admin_id'=>99],$extra));}
function line($po,$product,$quantity=5,$variant=null){return $po->items()->create(['product_id'=>$product->id,'product_variant_id'=>$variant?->id,'item_name'=>$product->title,'sku'=>$variant?->sku??$product->sku,'quantity_ordered'=>$quantity,'quantity_received'=>0,'unit_cost'=>10]);}
'''
(T/'bootstrap.php').write_text(s,encoding='utf-8')
r=(R/'commerce-menu-audit/_checks/routes.php').read_text(encoding='utf-8-sig')
r=r.replace("['admin.orders.','admin.payment-verifications.','admin.products.','admin.product-categories.','admin.product-tags.','admin.coupons.','customer.orders.']", "['admin.inventory-alerts.','admin.inventory-history.','admin.inventory-reports.','admin.stock-valuation.','admin.reorder-dashboard.','admin.purchase-orders.','admin.suppliers.','admin.products.inventory.']")
r=r.replace("['admin/orders','admin/payment-verifications','admin/products','admin/product-categories','admin/product-tags','admin/coupons','customer/orders']", "['admin/inventory-alerts','admin/inventory-history','admin/inventory-reports','admin/stock-valuation','admin/reorder-dashboard','admin/purchase-orders','admin/suppliers']")
(T/'routes.php').write_text(r,encoding='utf-8')
print('Prepared isolated inventory/purchasing fixtures and route audit.')
