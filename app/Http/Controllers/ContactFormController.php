<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\FormSubmission;

class ContactFormController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'fullName' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'emailAddress' => 'required|email',
            'phoneNumber' => 'required|string|max:50',
            'messageBox' => 'nullable|string',
        ]);

        FormSubmission::create([
    'form_source' => 'contact',
    'full_name'  => $data['fullName'],
    'subject'   => $data['subject'],
    'email'       => $data['emailAddress'],
    'phone'       => $data['phoneNumber'],
    'message'     => $data['messageBox'] ?? null,
]);

        Mail::send('emails.contact-form', $data, function ($message) use ($data) {
            $message->to([
                 'arizonaoutfits@gmail.com',
                'aq5431231@gmail.com',
            ])
            ->subject('New Contact Form Submission - ' . $data['subject'])
            ->replyTo($data['emailAddress']);
        });

        return redirect()->route('thank-you');
    }
}