<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();$product=App\Models\Product::where('status','active')->first();echo json_encode(['product'=>$product?'/product/'.$product->slug:null]);

