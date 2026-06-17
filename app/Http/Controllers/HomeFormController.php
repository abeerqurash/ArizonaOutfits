<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\FormSubmission;

class HomeFormController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'formType'     => 'required',
            'fullName'     => 'required|string|max:255',
            'subject'      => 'required|string|max:255',
            'emailAddress' => 'required|email',
            'phoneNumber'  => 'required|string|max:255',
            'messageBox'   => 'nullable|string',
        ]);

        // Save to database
        FormSubmission::create([
            'form_source' => 'home',
            'form_type'   => $data['formType'],
            'full_name'   => $data['fullName'],
            'subject'     => $data['subject'],
            'email'       => $data['emailAddress'],
            'phone'       => $data['phoneNumber'],
            'message'     => $data['messageBox'] ?? null,
        ]);

        // Send email
        Mail::send('emails.home-form', $data, function ($message) use ($data) {
            $message->to([
                'arizonaoutfits@gmail.com',
                'aq5431231@gmail.com',
            ])
            ->subject('New Home Form Submission')
            ->replyTo($data['emailAddress']);
        });

        return redirect()->route('thank-you');
    }
}