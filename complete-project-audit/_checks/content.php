<?php
require __DIR__.'/bootstrap.php';
use App\Models\{User,Product,Review,Order,Post,Category,PostRevision,PostRedirect};
use Illuminate\Support\Facades\{DB,Auth,Storage};
$customers=new App\Http\Controllers\Admin\CustomerController;
$reviews=new App\Http\Controllers\Admin\ReviewController;
$posts=new App\Http\Controllers\Admin\PostController;
$categories=new App\Http\Controllers\Admin\CategoryController;
$publicReviews=new App\Http\Controllers\ReviewController;
$product=Product::create(['title'=>'Review product','slug'=>'review-product','status'=>'active','stock'=>3,'regular_price'=>20]);
function account($extra=[]){static $i=0;return User::create(array_replace(['name'=>'Customer '.++$i,'email'=>'c'.$i.'@example.test','status'=>'active','is_admin'=>false],$extra));}
function articleData($extra=[]){return array_replace(['title'=>'Audit article','status'=>'draft','template'=>'arizona-blog-system-test','robots_index'=>1,'robots_follow'=>1],$extra);}

run('Customer listing, filtering and safeguards',function()use($customers){
    $a=account(['name'=>'Filter Customer','phone'=>'+923001234567']);$adminUser=account(['is_admin'=>true]);
    $list=$customers->index(req(['search'=>'1234567']))->getData()['customers'];check('Customer search finds phone',$list->contains('id',$a->id));
    check('Legacy administrator excluded from customer list',!$customers->index(req())->getData()['customers']->contains('id',$adminUser->id));
    try{$customers->show($adminUser);check('Admin cannot be viewed as customer',false);}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check('Admin cannot be viewed as customer',$e->getStatusCode()===404);}
    check('Customer status validates allowed values',validationBlocked(fn()=>$customers->update(req(['name'=>'Valid','status'=>'unexpected']),$a)));
    $customers->update(req(['name'=>'Renamed customer','status'=>'blocked']),$a);check('Customer name and blocked status save',$a->fresh()->name==='Renamed customer' && $a->fresh()->status==='blocked');
    check('Blocked filter works',$customers->index(req(['status'=>'blocked']))->getData()['customers']->contains('id',$a->id));
    $unused=account();$customers->destroy($unused);check('Unreferenced customer can be deleted',User::find($unused->id)===null);
});
run('Archived orders and spending',function()use($customers){
    $a=account(['name'=>'Archived spender']);$paid=Order::create(['user_id'=>$a->id,'order_number'=>'PAID-ARCHIVE','order_status'=>'completed','payment_status'=>'paid','total'=>50,'currency'=>'USD']);$paid->delete();
    Order::create(['user_id'=>$a->id,'order_number'=>'UNPAID','order_status'=>'pending','payment_status'=>'pending','total'=>30,'currency'=>'USD']);
    Order::create(['user_id'=>$a->id,'order_number'=>'REFUNDED','order_status'=>'refunded','payment_status'=>'refunded','total'=>10,'currency'=>'USD']);
    $data=$customers->show($a)->getData();check('Customer detail includes archived orders',$data['customer']->orders->contains('id',$paid->id));
    $total=$data['totalSpent'];check('Customer spending counts paid purchases only',is_array($total)?($total['USD']??0)===50.0:(float)$total===50.0,'Expected USD 50');
    $item=$customers->index(req(['search'=>'Archived spender']))->getData()['customers']->first();check('Customer order count includes archived orders',$item->orders_count===3);
    $archivedOnly=account();$order=Order::create(['user_id'=>$archivedOnly->id,'order_number'=>'ONLY-ARCHIVED','order_status'=>'completed','payment_status'=>'paid','total'=>25,'currency'=>'USD']);$order->delete();
    $customers->destroy($archivedOnly);check('Customer deletion blocked when only archived orders exist',User::find($archivedOnly->id)!==null);
});
run('Review moderation and public visibility',function()use($product,$reviews){
    $review=Review::create(['product_id'=>$product->id,'user_id'=>1,'name'=>'Reviewer','email'=>'reviewer@example.test','rating'=>4,'review'=>'Useful product review','status'=>'pending']);
    check('Pending reviews hidden publicly',$product->approvedReviews()->count()===0);
    $reviews->update(req(['status'=>'approved']),$review);check('Approved review appears publicly',$product->approvedReviews()->count()===1);
    $data=$reviews->index(req(['search'=>'Useful','rating'=>4,'status'=>'approved']))->getData();check('Review search/status/rating filters combine',$data['reviews']->contains('id',$review->id));
    check('Review status validates allowed values',validationBlocked(fn()=>$reviews->update(req(['status'=>'invalid']),$review)));
    $reviews->update(req(['status'=>'rejected']),$review);check('Rejected review disappears publicly',$product->approvedReviews()->count()===0);
    $reviews->destroy($review);check('Review deletion removes record',Review::find($review->id)===null);
});
run('Public review account/product guards',function()use($publicReviews,$product,$app){
    $user=account(['status'=>'blocked']);Auth::guard('web')->setUser($user);Auth::shouldUse('web');
    try{$publicReviews->store(req(['rating'=>4,'review'=>'This is a sufficiently long review']),$product);check('Blocked customer cannot submit reviews',false);}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check('Blocked customer cannot submit reviews',$e->getStatusCode()===403);}catch(Illuminate\Validation\ValidationException){check('Blocked customer cannot submit reviews',true);}
    Auth::guard('web')->forgetUser();Auth::shouldUse('web');$inactive=Product::create(['title'=>'Hidden','slug'=>'hidden','status'=>'inactive']);
    try{$publicReviews->store(req(['name'=>'Guest','email'=>'guest@example.test','rating'=>4,'review'=>'This is a sufficiently long review']),$inactive);check('Inactive product cannot receive reviews',false);}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check('Inactive product cannot receive reviews',$e->getStatusCode()===404);}
    Auth::guard('web')->setUser(User::find(1));Auth::shouldUse('admin');
    $before=Review::count();$publicReviews->store(req(['rating'=>5,'review'=>'A helpful review from the real customer']),$product);$review=Review::latest('id')->first();check('Public reviews use customer guard when admin coexists',$review->user_id===1 && Review::count()===$before+1);
    $before=Review::count();$publicReviews->store(req(['rating'=>5,'review'=>'Another sufficiently long customer review']),$product);check('Duplicate customer review blocked',Review::count()===$before);
    Auth::shouldUse('admin');
});
run('Blog category hierarchy',function()use($categories){
    $categories->store(req(['title'=>'Root category']));$root=Category::latest('id')->first();$categories->store(req(['title'=>'Child category','parent_id'=>$root->id]));$child=Category::latest('id')->first();
    check('Category create assigns parent',$child->parent_id===$root->id);
    check('Category self-parent rejected',validationBlocked(fn()=>$categories->update(req(['title'=>'Root','parent_id'=>$root->id]),$root)));
    check('Category descendant-parent rejected',validationBlocked(fn()=>$categories->update(req(['title'=>'Root','parent_id'=>$child->id]),$root)));
    $categories->destroy($root);check('Category with children cannot be deleted',Category::find($root->id)!==null);
    $categories->update(req(['title'=>'Renamed child']),$child);check('Category update can move child to root',$child->fresh()->parent_id===null);
    $categories->destroy($child);check('Unused category deletion works',Category::find($child->id)===null);
});
run('Blog CRUD, categories, redirect and revision',function()use($posts,$categories){
    $category=Category::create(['title'=>'Article category','slug'=>'article-category']);
    $posts->store(req(articleData(['categories'=>[$category->id],'primary_category_id'=>$category->id])));$post=Post::latest('id')->first();
    check('Post creation records administrator author',$post->author_id===99);
    check('Post creation attaches categories and primary',$post->categories()->count()===1 && $post->primary_category_id===$category->id);
    check('Draft hidden from public scope',!Post::published()->whereKey($post->id)->exists());
    $categories->destroy($category);check('Category used by posts cannot be deleted',Category::find($category->id)!==null);
    $posts->update(req(articleData(['title'=>'Revised article','slug'=>'revised-article','categories'=>[$category->id],'primary_category_id'=>$category->id])),$post);
    $post->refresh();check('Post update saves revision snapshot',$post->revisions()->count()===1);
    check('Slug update creates old URL redirect',PostRedirect::where('old_slug','audit-article')->where('post_id',$post->id)->exists());
    $revision=$post->revisions()->first();$posts->restoreRevision($post,$revision);$post->refresh();check('Revision restores original title and slug',$post->title==='Audit article' && $post->slug==='audit-article');
    check('Revision restore retains safety snapshot',$post->revisions()->count()===2);
    $posts->store(req(articleData(['title'=>'Other article'])));$other=Post::latest('id')->first();
    try{$posts->restoreRevision($other,$revision);check('Cross-post revision restore rejected',false);}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check('Cross-post revision restore rejected',$e->getStatusCode()===404);}
    $posts->destroy($other);check('Post deletion works',Post::find($other->id)===null);
    check('Invalid publishing status rejected',validationBlocked(fn()=>$posts->store(req(articleData(['status'=>'unknown'])))));
    check('Scheduled post requires date',validationBlocked(fn()=>$posts->store(req(articleData(['status'=>'scheduled'])))));
    $posts->store(req(articleData(['title'=>'Scheduled article','status'=>'scheduled','scheduled_at'=>now()->addDay()])));$scheduled=Post::latest('id')->first();check('Scheduled post stays hidden before publishing',!Post::published()->whereKey($scheduled->id)->exists());
    $posts->store(req(articleData(['title'=>'Published article','status'=>'published'])));$published=Post::latest('id')->first();check('Published post visible in public scope',Post::published()->whereKey($published->id)->exists());
    check('Publishing missing custom Blade template rejected',validationBlocked(fn()=>$posts->store(req(articleData(['title'=>'Missing template','status'=>'published','template'=>'audit-missing-template'])))));
    check('Unsafe template name rejected',validationBlocked(fn()=>$posts->store(req(articleData(['template'=>'../layouts.app'])))));
});
run('Category shared-media protection',function()use($categories){
    Storage::disk('public')->put('categories/shared.png','fixture');$a=Category::create(['title'=>'Shared A','slug'=>'shared-a','image'=>'categories/shared.png']);$b=Category::create(['title'=>'Shared B','slug'=>'shared-b','image'=>'categories/shared.png']);
    $categories->destroy($a);check('Deleting category preserves image used by another category',Storage::disk('public')->exists('categories/shared.png'));
});
$pages=['Customers'=>fn()=>$customers->index(req()),'Customer detail'=>fn()=>$customers->show(User::find(1)),'Reviews'=>fn()=>$reviews->index(req()),'Blog Posts'=>fn()=>$posts->index(req()),'Post create'=>fn()=>$posts->create(),'Post edit'=>fn()=>$posts->edit(Post::first()),'Blog Categories'=>fn()=>$categories->index(req()),'Category create'=>fn()=>$categories->create(),'Category edit'=>fn()=>$categories->edit(Category::first())];
foreach($pages as $label=>$factory)run('Render '.$label,function()use($label,$factory){check('Page render: '.$label,strlen($factory()->render())>500);});
foreach(['admin.customers.index','admin.customers.update','admin.customers.destroy','admin.reviews.index','admin.reviews.update','admin.reviews.destroy','admin.posts.index','admin.posts.store','admin.posts.revisions.restore','admin.categories.index','admin.categories.store','admin.categories.destroy'] as $name) run('Permission '.$name,function()use($app,$name){
    $mock=Mockery::mock(App\Models\Admin::class)->makePartial();$mock->shouldReceive('isActive')->andReturn(true);$mock->shouldReceive('hasAdminPermission')->andReturn(false);Auth::guard('admin')->setUser($mock);$r=req();$r->setRouteResolver(fn()=>$app['router']->getRoutes()->getByName($name));
    try{(new App\Http\Middleware\AdminMiddleware)->handle($r,fn()=>response('allowed'));check('Unauthorized admin denied: '.$name,false);}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check('Unauthorized admin denied: '.$name,$e->getStatusCode()===403);}
});
Auth::guard('admin')->setUser($admin);
$output=getenv('CUSTOMER_CONTENT_STAGE')?__DIR__.'/../../customer-content-fixes/checks.json':__DIR__.'/../checks.json';file_put_contents($output,json_encode($results,JSON_PRETTY_PRINT));
echo 'COMPLETED '.count($results).' checks: '.count(array_filter($results,fn($r)=>$r['result']==='PASS')).' PASS, '.count(array_filter($results,fn($r)=>$r['result']==='ISSUE')).' ISSUE, '.count(array_filter($results,fn($r)=>$r['result']==='ERROR'))." ERROR. SQLite memory only.\n";
