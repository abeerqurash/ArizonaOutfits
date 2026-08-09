<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminEmailTemplateController extends AdminController
{
    public function index(): View
    {
        $templates = EmailTemplate::query()->with('editor:id,name')->orderBy('name')->get();
        return view('admin.email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required','string','max:255'],
            'body' => ['required','string','max:50000'],
            'is_enabled' => ['nullable','boolean'],
        ]);

        $this->validateVariables($request, $emailTemplate, $validated['subject'] . ' ' . $validated['body']);

        $emailTemplate->update([
            'subject'=>$validated['subject'],
            'body'=>$validated['body'],
            'is_enabled'=>$request->boolean('is_enabled'),
            'updated_by'=>$request->user()->id,
        ]);

        return redirect()->route('admin.email-templates.edit', $emailTemplate)->with('success', 'Email template updated.');
    }

    public function preview(EmailTemplate $emailTemplate, EmailTemplateService $service): View
    {
        $variables = $this->sampleVariables($emailTemplate);
        return view('emails.dynamic-template', [
            'renderedSubject'=>$service->replace($emailTemplate->subject, $variables, false),
            'renderedBody'=>$service->sanitizeHtml($service->replace($emailTemplate->body, $variables, true)),
        ]);
    }

    public function sendTest(Request $request, EmailTemplate $emailTemplate, EmailTemplateService $service): RedirectResponse
    {
        $validated = $request->validate(['email'=>['required','email:rfc','max:255']]);
        $variables = $this->sampleVariables($emailTemplate);
        $subject = $service->replace($emailTemplate->subject, $variables, false);
        $body = $service->sanitizeHtml($service->replace($emailTemplate->body, $variables, true));

        Mail::send('emails.dynamic-template', ['renderedSubject'=>$subject,'renderedBody'=>$body], function ($message) use ($validated, $subject): void {
            $message->to($validated['email'])->subject('[TEST] ' . $subject);
        });

        return back()->with('success', 'Test email sent to ' . $validated['email'] . '.');
    }

    private function validateVariables(Request $request, EmailTemplate $template, string $content): void
    {
        preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);
        $unknown = array_diff(array_unique($matches[1] ?? []), $template->available_variables ?? []);
        if ($unknown) {
            $request->validate(['body'=>[fn ($attribute,$value,$fail) => $fail('Unknown variable(s): ' . implode(', ', $unknown))]]);
        }
    }

    private function sampleVariables(EmailTemplate $template): array
    {
        $defaults = [
            'customer_name'=>'Alex Customer','order_number'=>'AO-10025','order_total'=>'149.99','currency'=>'USD',
            'payment_status'=>'Paid','order_url'=>url('/dashboard'),'admin_order_url'=>url('/admin/orders/10025'),
            'alert_type'=>'Low stock','item_name'=>'Classic Arizona Hoodie — Black / Medium','current_stock'=>'3',
            'threshold'=>'5','inventory_url'=>url('/admin/inventory-alerts'),'store_name'=>config('app.name'),
        ];
        return collect($template->available_variables ?? [])->mapWithKeys(fn ($key) => [$key=>$defaults[$key] ?? 'Sample value'])->all();
    }
}
