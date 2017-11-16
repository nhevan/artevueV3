<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\EmailTemplate;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use App\Mail\AnnouncementEmail;
use App\Events\NewBuyPostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MailsController extends ApiController
{
	protected $request;
	protected $templates;

	public function __construct(Request $request, EmailTemplate $templates)
	{
		$this->request = $request;
		$this->templates = $templates;
	}

	/**
	 * lists all available templates that are editable
	 * @return [type] [description]
	 */
    public function templates()
    {
    	$templates = $this->templates->all();

    	return view('mails.templates', compact('templates'));
    }

    /**
     * previews a email template on the browser
     * @param  EmailTemplate $template [description]
     * @return [type]                  [description]
     */
    public function preview(EmailTemplate $template)
    {
    	$mail = $this->buildMailFromTemplate($template);

        return $mail->preview()['html'];
    }

    /**
     * shows the page for editing email templates
     * @param  EmailTemplate $template [description]
     * @return [type]                  [description]
     */
    public function edit(EmailTemplate $template)
    {
    	return view('mails.edit', compact('template'));
    }

    /**
     * updates the email template
     * @param  EmailTemplate $template [description]
     * @return [type]                  [description]
     */
    public function update(EmailTemplate $template)
    {
    	$template->content = $this->request->content;
    	$template->sender_email = $this->request->sender_email;
    	$template->sender_name = $this->request->sender_name;
    	$template->subject = $this->request->subject;
    	$template->save();

    	$this->request->session()->flash('status', 'Email template successfully updated!');
    	return redirect()->route('mail.templates');
    }

    /**
     * sends a test email to the authenticated user for the given email template
     * @param  EmailTemplate $template [description]
     * @return [type]                  [description]
     */
    public function test(EmailTemplate $template)
    {
    	$mail = $this->buildMailFromTemplate($template);
    	Mail::to(Auth::user()->email)->queue($mail);

    	$this->request->session()->flash('status', 'Test email sent to your email address !');
    	return redirect()->route('mail.templates');
    }

    /**
     * builds an email object from given template
     * @param  EmailTemplate $template [description]
     * @return [type]                  [description]
     */
    public function buildMailFromTemplate(EmailTemplate $template)
    {
    	$mail_class = $template->mail_class;
    	if (in_array($mail_class, ['App\Mail\WelcomeEmail', 'App\Mail\AnnouncementEmail'])) {
	    	return new $mail_class(Auth::user());
    	}

    	if (in_array($mail_class, ['App\Mail\NewPasswordEmail'])) {
			return new $mail_class(Auth::user(), 1);
    	}

    	if (in_array($mail_class, ['App\Mail\BuyPostRequestMail'])) {
    		$interested_user = User::where('username', 'nhevan')->first();
    		$post = Post::where('owner_id', Auth::user()->id)->first();
    		$buy_request = new NewBuyPostRequest($interested_user, $post);

			return new $mail_class($buy_request);
    	}
    }

    /**
     * Sends announcement email to all artevue users
     * @return [type] [description]
     */
    public function dispatchAnnouncement()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            Mail::to($user->email)->queue(new AnnouncementEmail($user));
        }
        
        $this->request->session()->flash('status', 'Announcement emails are now being sent to all Artevue users.');
        return redirect()->route('mail.templates');
    }
}
