<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class HomeFormController extends Controller
{
    /**
     * Handle Contact Us / PR submissions from both:
     * - Homepage
     * - Contact page
     */
    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'formSource' => [
                'required',
                'string',
                Rule::in(['home', 'contact']),
            ],

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
            'formSource.required' => 'The form source is missing.',
            'formSource.in' => 'The form source is invalid.',

            'formType.required' => 'Please select Contact Us or PR.',
            'formType.in' => 'Please select a valid enquiry type.',

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

            'prOrganization.required_if' => 'Please enter your publication, company, agency, or organization.',
            'prOrganization.max' => 'The organization name may not be greater than 255 characters.',

            'prEnquiryType.required_if' => 'Please select the type of PR enquiry.',
            'prEnquiryType.in' => 'Please select a valid PR enquiry type.',

            'prWebsite.url' => 'Please enter a valid website or social profile URL, including https://.',
            'prWebsite.max' => 'The website or social profile URL is too long.',
        ]);

        $isPr = $data['formType'] === 'PR';

        /*
         * The current FormSubmission structure does not yet have dedicated
         * PR columns, so PR-specific details are preserved in the message
         * without assuming database columns that may not exist.
         */
        $storedMessage = $data['messageBox'] ?? null;

        if ($isPr) {
            $prDetails = [
                'PR Organization: ' . $data['prOrganization'],
                'PR Enquiry Type: ' . $data['prEnquiryType'],
            ];

            if (!empty($data['prWebsite'])) {
                $prDetails[] = 'Website / Social: ' . $data['prWebsite'];
            }

            if (!empty($storedMessage)) {
                $prDetails[] = '';
                $prDetails[] = 'Message:';
                $prDetails[] = $storedMessage;
            }

            $storedMessage = implode(PHP_EOL, $prDetails);
        }

        FormSubmission::create([
            'form_source' => $data['formSource'],
            'form_type' => $data['formType'],
            'full_name' => $data['fullName'],
            'subject' => $data['subject'],
            'email' => $data['emailAddress'],
            'phone' => $data['phoneNumber'],
            'message' => $storedMessage,
        ]);

        $emailData = array_merge($data, [
            'isPr' => $isPr,
        ]);

        /*
         * Keep the existing home-form email template as the single template
         * for the shared Contact/PR submission flow.
         */
        Mail::send(
            'emails.home-form',
            $emailData,
            function ($message) use ($data, $isPr) {
                $submissionLabel = $isPr
                    ? 'PR Enquiry'
                    : 'Contact Enquiry';

                $sourceLabel = $data['formSource'] === 'contact'
                    ? 'Contact Page'
                    : 'Homepage';

                $message
                    ->to([
                        'arizonaoutfits@gmail.com',
                        'aq5431231@gmail.com',
                    ])
                    ->subject(
                        'New '
                        . $submissionLabel
                        . ' - '
                        . $sourceLabel
                        . ' - '
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
