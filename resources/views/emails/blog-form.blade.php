<h2>New Blog Form Submission</h2>

<p><strong>Form Type:</strong> {{ $formType }}</p>

<p><strong>Name:</strong> {{ $fullName }}</p>

<p><strong>Subject:</strong> {{ $subject }}</p>

<p><strong>Email:</strong> {{ $emailAddress }}</p>

<p><strong>Phone:</strong> {{ $phoneNumber }}</p>

<p><strong>Message:</strong></p>

<p>{{ $messageBox }}</p>

<p><strong>Blog:</strong> {{ $blog_title ?? request('blog_title', 'N/A') }}</p>
<p><strong>Slug:</strong> {{ $blog_slug ?? request('blog_slug', 'N/A') }}</p>