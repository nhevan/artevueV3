@component('mail::message')
Dear {{ $event->post->owner->name }},
<br>
<br>
{{ $event->interested_user->name }} is interested to buy the following art from you:
<img src="{{ $cloudfront_url.$event->post->image }}" alt="Art Work">
<br>
<br>
Additional information goes here.
<br>
<br>
Thank you,
<br>
<br>
![ArteVue Logo](http://dy01r176shqrv.cloudfront.net/artevue-only-logo-resized-v2.png)
<br/>
Sho Ahad-Choudhury<br>
<br>
<strong>Founder and Chief Executive Officer</strong><br>
Email : <hello@artevue.co.uk><br>
Website : [www.artevue.co.uk](www.artevue.co.uk)
<br>
<br>
This email and any attachment is confidential, may be legally privileged and must not be disclosed to or used by anyone other than the intended recipient. Unauthorised use, disclosure, distribution or copying is prohibited and may be unlawful. If you are not the intended recipient, please notify <hello@artevue.co.uk> and then delete this email. 
<br>
<br>
This email is sent over a public network and its completeness or accuracy cannot be guaranteed. Any attached files were checked with virus detection software before sending, but you should carry out your own virus check before opening them and we do not accept liability for any loss or damage caused by software viruses.
@endcomponent