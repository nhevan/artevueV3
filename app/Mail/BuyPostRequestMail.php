<?php

namespace App\Mail;

use App\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Events\NewBuyPostRequest;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BuyPostRequestMail extends Mailable
{
    public $event;
    public $content;
    private $template;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(NewBuyPostRequest $event)
    {
        $this->event = $event;
        $this->template = EmailTemplate::where('mail_class', get_class($this))->first();
        $this->content = $this->replaceVariables();
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
        $content = str_replace('$owner_name', $this->event->post->owner->name, $this->template->content);
        $content = str_replace('$requester_id', $this->event->interested_user->id, $content);
        $content = str_replace('$requester_name', $this->event->interested_user->name, $content);
        $content = str_replace('dummy.png', $this->event->post->image, $content);

        return $content;
    }
}
