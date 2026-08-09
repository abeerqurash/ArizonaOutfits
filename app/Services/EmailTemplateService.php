<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Schema;

class EmailTemplateService
{
    public function render(string $slug, array $variables, string $fallbackSubject, ?string $fallbackView = null): array
    {
        $template = Schema::hasTable('email_templates')
            ? EmailTemplate::query()->where('slug', $slug)->where('is_enabled', true)->first()
            : null;

        if (!$template) {
            return ['subject'=>$fallbackSubject,'html'=>null,'view'=>$fallbackView,'template'=>null];
        }

        return [
            'subject' => $this->replace($template->subject, $variables, false),
            'html' => $this->sanitizeHtml($this->replace($template->body, $variables, true)),
            'view' => 'emails.dynamic-template',
            'template' => $template,
        ];
    }

    public function replace(string $content, array $variables, bool $escapeValues = true): string
    {
        foreach ($variables as $name => $value) {
            $replacement = $escapeValues ? e((string) $value) : strip_tags((string) $value);
            $content = str_replace('{{' . $name . '}}', $replacement, $content);
        }

        return preg_replace('/\{\{[a-zA-Z0-9_]+\}\}/', '', $content) ?? $content;
    }

    public function sanitizeHtml(string $html): string
    {
        $html = strip_tags($html, '<h1><h2><h3><p><br><strong><b><em><i><u><ul><ol><li><a><table><thead><tbody><tr><th><td><hr><blockquote>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/(href\s*=\s*["\'])\s*(javascript|data):/i', '$1#', $html) ?? $html;
        return $html;
    }
}
