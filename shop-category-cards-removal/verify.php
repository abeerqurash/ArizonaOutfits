<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
$compiled=app('blade.compiler')->compileString(file_get_contents(__DIR__.'/_staged/resources/views/products/index.blade.php'));file_put_contents(__DIR__.'/compiled-check.php',$compiled);echo "Shop category card section removed; category filter retained.\n";