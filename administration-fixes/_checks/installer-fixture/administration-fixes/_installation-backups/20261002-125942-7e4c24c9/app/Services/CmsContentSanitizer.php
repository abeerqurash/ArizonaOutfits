<?php

namespace App\Services;

class CmsContentSanitizer
{
    public function clean(?string $html): string
    {
        $html=strip_tags((string)$html,'<h1><h2><h3><h4><p><br><strong><b><em><i><u><ul><ol><li><a><img><table><thead><tbody><tr><th><td><hr><blockquote><code><pre>');
        $html=preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i','',$html)??$html;
        $html=preg_replace('/((?:href|src)\s*=\s*["\'])\s*(?:javascript|data):/i','$1#',$html)??$html;
        $html=preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is','',$html)??$html;
        return trim($html);
    }
}
