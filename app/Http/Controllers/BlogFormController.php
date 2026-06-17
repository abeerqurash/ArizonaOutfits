<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\FormSubmission;

class BlogFormController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
    'formType' => 'required',
    'fullName' => 'required|string|max:255',
    'subject' => 'required|string|max:255',
    'emailAddress' => 'required|email',
    'phoneNumber' => 'required|string|max:255',
    'messageBox' => 'nullable|string',
    'blog_title' => 'nullable|string|max:255',
    'blog_slug' => 'nullable|string|max:255',
]);

FormSubmission::create([
    'form_source' => 'blog',
    'form_type'   => $data['formType'],
    'full_name'   => $data['fullName'],
    'subject'     => $data['subject'],
    'email'       => $data['emailAddress'],
    'phone'       => $data['phoneNumber'],
    'message'     => $data['messageBox'] ?? null,
    'blog_title'  => $data['blog_title'] ?? null,
    'blog_slug'   => $data['blog_slug'] ?? null,
]);

        try {

            Mail::send('emails.blog-form', $data, function ($message) use ($data) {

                $message->to([
                    'arizonaoutfits@gmail.com',
                'aq5431231@gmail.com',
                ])
                ->subject('New Blog Form Submission')
                ->replyTo($data['emailAddress']);
            });

            return redirect()->route('thank-you');

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());

        }
    }
}