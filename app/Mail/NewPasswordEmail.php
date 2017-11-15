<?php

namespace App\Mail;

use App\User;
use App\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    public $content;
    private $template;
    private $new_password = 'PreviewPassword';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $is_previewing = 0)
    {
        $this->user = $user;

        $this->template = EmailTemplate::where('mail_class', get_class($this))->first();
        $this->content = $this->replaceVariables();

        if (!$is_previewing) {
            $this->new_password = str_random(8);
            $this->user->password = bcrypt($new_password);
            $this->user->save();
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => $this->template->sender_email, 'name' => $this->template->sender_name])
                    ->subject($this->template->subject)
                    ->markdown('emails.template');
    }

    /**
     * provides a preview of the email to the browser
     * @return [type] [description]
     */
    public function preview()
    {
        $this->build();
        return $this->buildView();
    }

    /**
     * replaces the placeholders variables in the template with actual values
     * @return [type] [description]
     */
    public function replaceVariables()
    {
        $content = str_replace('$name', $this->user->name, $this->template->content);
        $content = str_replace('$username', $this->user->username, $content);
        $content = str_replace('$password', $this->new_password, $content);

        return $content;
    }
}
