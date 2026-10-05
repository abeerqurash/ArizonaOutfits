<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    /**
     * Handle the dedicated Contact page form.
     */
    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fullName' => [
                'required',
                'string',
                'max:255',
            ],
            'subject' => [
                'required',
                'string',
                'max:255',
            ],
            'emailAddress' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'phoneNumber' => [
                'required',
                'string',
                'max:30',
                'regex:/^\+?[0-9\s().-]{7,30}$/',
            ],
            'messageBox' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'fullName.required' => 'Please enter your full name.',
            'fullName.max' => 'Your name may not be greater than 255 characters.',

            'subject.required' => 'Please enter a subject.',
            'subject.max' => 'The subject may not be greater than 255 characters.',

            'emailAddress.required' => 'Please enter your email address.',
            'emailAddress.email' => 'Please enter a valid email address.',
            'emailAddress.max' => 'Your email address may not be greater than 255 characters.',

            'phoneNumber.required' => 'Please enter your phone number.',
            'phoneNumber.max' => 'Your phone number may not be greater than 30 characters.',
            'phoneNumber.regex' => 'Please enter a valid phone number using numbers and standard phone characters only.',

            'messageBox.max' => 'Your message may not be greater than 5000 characters.',
        ]);

        FormSubmission::create([
            'form_source' => 'contact',
            'full_name' => $data['fullName'],
            'subject' => $data['subject'],
            'email' => $data['emailAddress'],
            'phone' => $data['phoneNumber'],
            'message' => $data['messageBox'] ?? null,
        ]);

        Mail::send(
            'emails.contact-form',
            $data,
            function ($message) use ($data) {
                $message
                    ->to([
                        'arizonaoutfits@gmail.com',
                        'aq5431231@gmail.com',
                    ])
                    ->subject(
                        'New Contact Form Submission - '
                        . $data['subject']
                    )
                    ->replyTo(
                        $data['emailAddress'],
                        $data['fullName']
                    );
            }
        );

        return redirect()->route('thank-you');
    }
}
