<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class BlogFormController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'formType' => [
                'required',
                'string',
                Rule::in(['Contact Us', 'PR']),
            ],

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

            'blog_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'blog_slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'prOrganization' => [
                'exclude_unless:formType,PR',
                'required_if:formType,PR',
                'string',
                'max:255',
            ],

            'prEnquiryType' => [
                'exclude_unless:formType,PR',
                'required_if:formType,PR',
                'string',
                Rule::in([
                    'Press / Media Enquiry',
                    'Interview Request',
                    'Product Feature / Review',
                    'Brand Collaboration',
                    'Event / Appearance',
                    'Other PR Enquiry',
                ]),
            ],

            'prWebsite' => [
                'exclude_unless:formType,PR',
                'nullable',
                'url',
                'max:2048',
            ],
        ], [
            'phoneNumber.regex' => 'Please enter a valid phone number.',
            'prOrganization.required_if' => 'Publication / Company / Agency is required for PR enquiries.',
            'prEnquiryType.required_if' => 'Please select a PR enquiry type.',
            'prEnquiryType.in' => 'Please select a valid PR enquiry type.',
            'prWebsite.url' => 'Please enter a valid website or social profile URL.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Preserve PR details in the existing FormSubmission structure
        |--------------------------------------------------------------------------
        | The current form_submissions structure already stores the main message,
        | so PR details are appended there rather than requiring a database/schema
        | change during this validation pass.
        */
        $storedMessage = $data['messageBox'] ?? null;

        if ($data['formType'] === 'PR') {
            $prDetails = [
                'PR Organization: ' . $data['prOrganization'],
                'PR Enquiry Type: ' . $data['prEnquiryType'],
            ];

            if (!empty($data['prWebsite'])) {
                $prDetails[] = 'Website / Social Profile: ' . $data['prWebsite'];
            }

            if (!empty($storedMessage)) {
                $prDetails[] = '';
                $prDetails[] = 'Message:';
                $prDetails[] = $storedMessage;
            }

            $storedMessage = implode(PHP_EOL, $prDetails);
        }

        FormSubmission::create([
            'form_source' => 'blog',
            'form_type'   => $data['formType'],
            'full_name'   => $data['fullName'],
            'subject'     => $data['subject'],
            'email'       => $data['emailAddress'],
            'phone'       => $data['phoneNumber'],
            'message'     => $storedMessage,
            'blog_title'  => $data['blog_title'] ?? null,
            'blog_slug'   => $data['blog_slug'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Email data
        |--------------------------------------------------------------------------
        | Keep the existing emails.blog-form template working while also making
        | the validated PR values available when that template is upgraded.
        */
        $mailData = array_merge($data, [
            'isPr' => $data['formType'] === 'PR',
            'prOrganization' => $data['prOrganization'] ?? null,
            'prEnquiryType' => $data['prEnquiryType'] ?? null,
            'prWebsite' => $data['prWebsite'] ?? null,
        ]);

        try {
            Mail::send('emails.blog-form', $mailData, function ($message) use ($data) {
                $message->to([
                    'arizonaoutfits@gmail.com',
                    'aq5431231@gmail.com',
                ])
                ->subject(
                    $data['formType'] === 'PR'
                        ? 'New Blog PR Enquiry'
                        : 'New Blog Contact Enquiry'
                )
                ->replyTo($data['emailAddress'], $data['fullName']);
            });

            return redirect()->route('thank-you');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Your enquiry was saved, but the notification email could not be sent. Please try again later.');
        }
    }
}
