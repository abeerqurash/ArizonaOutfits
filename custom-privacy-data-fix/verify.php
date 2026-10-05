<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
app('view')->addNamespace('privacyFix',__DIR__);
$html=view('privacyFix::privacy-policy',['page'=>new App\Models\Page(['title'=>'Privacy Policy'])])->render();
echo strlen($html)>1000?'PASS: custom privacy template renders without controller-supplied posts/categories.':'FAIL';

