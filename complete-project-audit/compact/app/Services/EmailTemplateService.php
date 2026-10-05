<?php
namespace App\Services;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Schema;
class EmailTemplateService
{
    private const ORDER_STYLE_SLUGS = [
        'customer-order-confirmation',
        'admin-new-order',
    ];
    public function render(
        string $slug,
        array $variables,
        string $fallbackSubject,
        ?string $fallbackView = null
    ): array {
        $template = Schema::hasTable('email_templates')
            ? EmailTemplate::query()
                ->where('slug', $slug)
                ->where('is_enabled', true)
                ->first()
            : null;
        if (!$template) {
            return [
                'subject' => $fallbackSubject,
                'html' => null,
                'view' => $fallbackView,
                'template' => null,
            ];
        }
        return [
            'subject' => $this->replace(
                $template->subject,
                $variables,
                false
            ),
            'html' => $this->sanitizeHtml(
                $this->replace(
                    $template->body,
                    $variables,
                    true
                )
            ),
            'view' => $this->viewForSlug($slug),
            'template' => $template,
        ];
    }
    public function replace(string $content, array $variables, bool $escapeValues = true): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',function($m)use($variables,$escapeValues){
            $value=(string)($variables[$m[1]]??'');return $escapeValues?e($value):strip_tags($value);
        },$content)??'';
    }
    public function sanitizeHtml(string $html): string { return app(HtmlContentSanitizer::class)->clean($html); }
    public function viewForSlug(string $slug): string
    {
        if (in_array($slug, self::ORDER_STYLE_SLUGS, true)) {
            return 'emails.orders.dynamic-template';
        }
        return 'emails.dynamic-template';
    }
}