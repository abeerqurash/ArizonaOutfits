<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
file_put_contents(__DIR__.'/compiled.php',app('blade.compiler')->compileString(file_get_contents(__DIR__.'/app.blade.php')));
passthru('C:\\xampp\\php\\php.exe -l '.escapeshellarg(__DIR__.'/compiled.php'));
