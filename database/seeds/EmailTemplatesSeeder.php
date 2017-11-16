<?php

use App\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
	private $template;

	public function __construct(EmailTemplate $templates)
	{
		$this->templates = $templates;
	}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->newEmailTemplate(
        	'Welcome Email', 
        	'App\Mail\WelcomeEmail',
        	'noreply@artevue.co.uk',
        	'Artevue',
        	'Welcome to Artevue - Your Art Scene.',
        	'<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;">Dear $name,</p>
<h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2f3133; font-size: 19px; margin-top: 0px;">Welcome to Artevue - Your Art Scene.</h1>
<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;"><strong style="box-sizing: border-box;">Your Login details are:</strong><br /><strong style="box-sizing: border-box;">Username : $username</strong><br /><br />Thank you again for downloading ArteVue. We are very excited to see your art and also share the art on our App with you.&nbsp;<br /><br />ArteVue is about making the art world accessible to everyone, anywhere in the world, by connecting and sharing art between artists, enthusiast, collectors, galleries, art professionals and institutions.&nbsp;<br /><br /></p>
<h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2f3133; font-size: 19px; margin-top: 0px;">ArteVue places you in the center of your art world:</h1>
<ul style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: medium; box-sizing: border-box; line-height: 1.4;">
<li style="box-sizing: border-box;">Share, follow, discover, like and comment on art at a global level</li>
<li style="box-sizing: border-box;">Interact through personal messages or just follow artists, collectors, galleries, fairs etc, based on the art you like</li>
<li style="box-sizing: border-box;">Take a picture, tag, scan a label, comment, upload and share on multiple social platforms instantly</li>
<li style="box-sizing: border-box;">Build a virtual art collection, which you have, or just like! You don&rsquo;t have to be a collector to have an art collection</li>
<li style="box-sizing: border-box;">Label, document, curate and create a PDF catalogue of your collection to share with friends or clients</li>
<li style="box-sizing: border-box;">Be directly in touch with art professionals for advice or to just to find out which art fairs to go to</li>
<li style="box-sizing: border-box;">Buy/sell and even trade artworks by interacting with artists, collectors, galleries and professionals through the App</li>
<li style="box-sizing: border-box;">Keep ahead of the latest developments globally through our art daily news and events feed</li>
</ul>
<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;">Thank you and we look forward to seeing your art.&nbsp;<br /><br /><img style="box-sizing: border-box; max-width: 100%;" src="http://dy01r176shqrv.cloudfront.net/artevue-only-logo-resized-v2.png" alt="ArteVue Logo" /><br />Sho Ahad-Choudhury<br /><br /><strong style="box-sizing: border-box;">Founder and Chief Executive Officer</strong><br />Email :&nbsp;<a style="box-sizing: border-box; color: #3869d4;" href="mailto:submissions@artevue.co.uk">submissions@artevue.co.uk</a><br />Website :&nbsp;<a style="box-sizing: border-box; color: #3869d4;" href="www.artevue.co.uk">www.artevue.co.uk</a></p>',
        	'Available variables : $name, $username'
    	);

    	$this->newEmailTemplate(
        	'Password Email', 
        	'App\Mail\NewPasswordEmail',
        	'noreply@artevue.co.uk',
        	'Artevue',
        	'Forgot password email',
        	'<h1 style="font-family: Avenir, Helvetica, sans-serif; text-size-adjust: auto; box-sizing: border-box; color: #2f3133; font-size: 19px; margin-top: 0px;">Password Reset Request</h1>
<p style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #74787e; line-height: 1.5em; margin-top: 0px;">Dear $name,<br /><br />We have received a request to reset the password for your ArteVue account.<br /><br />Your username is :<span class="Apple-converted-space">&nbsp;</span><strong style="box-sizing: border-box;">$username</strong><br />Your new password is :<span class="Apple-converted-space">&nbsp;</span><strong style="box-sizing: border-box;">$password</strong><br /><br />Please log into the app using this username and the new password, which we have provided.<br /><br />Once you are in the app, please reset the password in Settings, which can be found under your Profile. Passwords must be between 8- 16 characters long and contain at least one number.<br /><br />If you did not request a new password please contact us immediately on<span class="Apple-converted-space">&nbsp;</span><a style="box-sizing: border-box; color: #3869d4;" href="applewebdata://9C9C8E31-FA39-480E-A86C-DF2E19703612/info@artevue.co.uk">info@artevue.co.uk</a><br /><br /></p>
<p style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #74787e; line-height: 1.5em; margin-top: 0px;">Best Regards,<br /><br />ArteVue Team</p>',
        	'Available variables : $name, $username, $password'
    	);

    	$this->newEmailTemplate(
        	'Announcement Email', 
        	'App\Mail\AnnouncementEmail',
        	'noreply@artevue.co.uk',
        	'Artevue',
        	'Announcement from Artevue.',
        	'<p><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">Dear $name,</span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">We have noted that some of our users are having problems connecting to our server after downloading v1.3.2 of ArteVue.</span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">If your ArteVue App is not working and showing a server connection error please can you delete ArteVue from your iPhone Home Screen and download a fresh version of the App from the App store. It should now work perfectly. Should you have any further issues or comments please can you email us at<span class="Apple-converted-space">&nbsp;</span></span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #3869d4;" href="mailto:hello@artevue.co.uk">hello@artevue.co.uk</a><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">ArteVue is happy to announce the launch of its inaugural ArtePrize</span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" href="http://airmail.calendar/2017-07-07%2012:00:00%20GMT+6">on Friday 7th July, 2017</a><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">. ArtePrize 2017 is a US$15,000 cash prize and an all-expenses paid 3 months artist residency at The Delfina Foundation in London. For more details about ArtePrize 2017 please visit:<span class="Apple-converted-space">&nbsp;</span></span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #3869d4;" href="applewebdata://97CF011C-518F-4B35-BAC7-18EE16E95584/www.artevue.co.uk">www.artevue.co.uk</a><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">Thank you,</span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><img style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; max-width: 100%;" src="http://dy01r176shqrv.cloudfront.net/artevue-only-logo-resized-v2.png" alt="ArteVue Logo" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;"><span class="Apple-converted-space">&nbsp;</span></span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">Sho Ahad-Choudhury</span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><strong style="color: #74787e; font-size: 16px; text-size-adjust: auto; font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">Founder and Chief Executive Officer</strong><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">Email :<span class="Apple-converted-space">&nbsp;</span></span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #3869d4;" href="mailto:hello@artevue.co.uk">hello@artevue.co.uk</a><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">Website :<span class="Apple-converted-space">&nbsp;</span></span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #3869d4;" href="applewebdata://97CF011C-518F-4B35-BAC7-18EE16E95584/www.artevue.co.uk">www.artevue.co.uk</a><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">This email and any attachment is confidential, may be legally privileged and must not be disclosed to or used by anyone other than the intended recipient. Unauthorised use, disclosure, distribution or copying is prohibited and may be unlawful. If you are not the intended recipient, please notify<span class="Apple-converted-space">&nbsp;</span></span><a style="font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto; box-sizing: border-box; color: #3869d4;" href="mailto:hello@artevue.co.uk">hello@artevue.co.uk</a><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;"><span class="Apple-converted-space">&nbsp;</span>and then delete this email.</span><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><br style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;" /><span style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; text-size-adjust: auto;">This email is sent over a public network and its completeness or accuracy cannot be guaranteed. Any attached files were checked with virus detection software before sending, but you should carry out your own virus check before opening them and we do not accept liability for any loss or damage caused by software viruses.</span></p>',
        	'Available variables : $name'
    	);

		$this->newEmailTemplate(
        	'Buy Post Request Email', 
        	'App\Mail\BuyPostRequestMail',
        	'noreply@artevue.co.uk',
        	'Artevue',
        	'You have a potential buyer for your artwork!',
        	'<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;">Dear $owner_name,</p>
<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;">$requester_name is interested to buy the following art from you:</p>
<p><img style="box-sizing: border-box; max-width: 100%; display: block; margin-left: auto; margin-right: auto;" src="http://dy01r176shqrv.cloudfront.net/dummy.png" alt="Art Work" /></p>
<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;">To reply to $requester_name&nbsp;please clink on the link below:</p>
<table class="action" style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: medium; box-sizing: border-box; margin: 30px auto; padding: 0px; text-align: center; width: 500px;" width="100%" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;" align="center">
<table style="box-sizing: border-box;" border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;" align="center">
<table style="box-sizing: border-box;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;"><a class="button button-green" style="box-sizing: border-box; border-radius: 3px; box-shadow: rgba(0, 0, 0, 0.16) 0px 2px 3px; color: #ffffff; display: inline-block; text-decoration-line: none; text-size-adjust: none; background-color: #2ab27b; border-width: 10px 18px; border-style: solid; border-color: #2ab27b;" href="artevue://conversations/$requester_id" target="_blank" rel="noopener">Open Artevue</a></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<p style="color: #74787e; font-family: Avenir, Helvetica, sans-serif; font-size: 16px; box-sizing: border-box; line-height: 1.5em; margin-top: 0px;"><br /><br />Thank you,&nbsp;<br /><br /><img style="box-sizing: border-box; max-width: 100%;" src="http://dy01r176shqrv.cloudfront.net/artevue-only-logo-resized-v2.png" alt="ArteVue Logo" />&nbsp;<br />ArteVue Team<br /><br /><br />This email and any attachment is confidential, may be legally privileged and must not be disclosed to or used by anyone other than the intended recipient. Unauthorised use, disclosure, distribution or copying is prohibited and may be unlawful. If you are not the intended recipient, please notify&nbsp;<a style="box-sizing: border-box; color: #3869d4;" href="mailto:hello@artevue.co.uk" target="_blank" rel="noopener">hello@artevue.co.uk</a>&nbsp;and then delete this email.&nbsp;<br /><br />This email is sent over a public network and its completeness or accuracy cannot be guaranteed. Any attached files were checked with virus detection software before sending, but you should carry out your own virus check before opening them and we do not accept liability for any loss or damage caused by software viruses.</p>',
        	'Available variables : $owner_name, $requester_id, $requester_name, dummy.png'
    	);
    }

    public function newEmailTemplate($name, $mail_class, $sender_email, $sender_name, $subject, $content, $additional_info)
    {
    	$template = $this->templates->where('mail_class', $mail_class)->first();
    	if (!$template) {
	        return factory('App\EmailTemplate')->create([
                    'name' => $name,
                    'mail_class' => $mail_class,
                    'sender_email' => $sender_email,
                    'sender_name' => $sender_name,
                    'subject' => $subject,
                    'content' => $content,
                    'additional_info' => $additional_info,
	        	]);
    	}
    }
}
