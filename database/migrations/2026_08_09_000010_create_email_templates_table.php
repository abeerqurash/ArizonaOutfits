<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->string('subject');
                $table->longText('body');
                $table->json('available_variables')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_system')->default(true);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $now = now();
        $templates = [
            ['Customer order confirmation','customer-order-confirmation','Sent to a customer after an order is created.','Order confirmation - {{order_number}}','<h1>Thank you for your order, {{customer_name}}</h1><p>We have received order <strong>{{order_number}}</strong>.</p><p>Order total: <strong>{{currency}} {{order_total}}</strong></p><p>Payment status: {{payment_status}}</p><p><a href="{{order_url}}">View your order</a></p>',['customer_name','order_number','order_total','currency','payment_status','order_url','store_name']],
            ['Administrator new order','admin-new-order','Sent to store administrators when a new order is received.','New order received - {{order_number}}','<h1>New order received</h1><p>Order <strong>{{order_number}}</strong> was placed by {{customer_name}}.</p><p>Total: <strong>{{currency}} {{order_total}}</strong></p><p><a href="{{admin_order_url}}">Open order in admin</a></p>',['customer_name','order_number','order_total','currency','payment_status','admin_order_url','store_name']],
            ['Inventory stock alert','inventory-alert','Sent when an item reaches low or zero stock.','{{alert_type}}: {{item_name}}','<h1>{{alert_type}}</h1><p><strong>{{item_name}}</strong> now has {{current_stock}} unit(s) available.</p><p>Alert threshold: {{threshold}}</p><p><a href="{{inventory_url}}">Review inventory alerts</a></p>',['alert_type','item_name','current_stock','threshold','inventory_url','store_name']],
        ];

        foreach ($templates as [$name,$slug,$description,$subject,$body,$variables]) {
            DB::table('email_templates')->updateOrInsert(['slug'=>$slug],[
                'name'=>$name,'description'=>$description,'subject'=>$subject,'body'=>$body,
                'available_variables'=>json_encode($variables),'is_enabled'=>true,'is_system'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
