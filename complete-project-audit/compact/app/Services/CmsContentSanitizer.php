<?php
namespace App\Services;
class CmsContentSanitizer { public function clean(?string $html): string { return app(HtmlContentSanitizer::class)->clean($html); } }