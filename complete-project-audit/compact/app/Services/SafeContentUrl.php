<?php
namespace App\Services;
class SafeContentUrl
{
    public static function allowed(string $url, bool $mailLinks = false): bool
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) return false;
        if (str_starts_with($url, '//')) return false;
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) return true;
        $parts = parse_url($url);
        if (!$parts || isset($parts['user']) || isset($parts['pass'])) return false;
        $scheme = strtolower($parts['scheme'] ?? '');
        if ($mailLinks && in_array($scheme, ['mailto','tel'], true)) return strlen($url) > strlen($scheme)+1;
        return in_array($scheme, ['http','https'], true) && (bool) filter_var($url, FILTER_VALIDATE_URL);
    }
    public static function internal(string $url): bool
    {
        if (!self::allowed($url)) return false;
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) return true;
        $parts=parse_url($url);
        return strtolower($parts['scheme']) === request()->getScheme()
            && strcasecmp($parts['host'],request()->getHost()) === 0
            && ($parts['port'] ?? ($parts['scheme']==='https'?443:80)) === request()->getPort();
    }
}