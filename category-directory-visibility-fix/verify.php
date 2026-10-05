<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
$s=file_get_contents(__DIR__.'/categories.blade.php');file_put_contents(__DIR__.'/compiled.php',app('blade.compiler')->compileString($s));
passthru('C:\\xampp\\php\\php.exe -l '.escapeshellarg(__DIR__.'/compiled.php'));
