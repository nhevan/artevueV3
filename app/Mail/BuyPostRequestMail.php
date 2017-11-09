<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Events\NewBuyPostRequest;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BuyPostRequestMail extends Mailable
{
    public $event;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(NewBuyPostRequest $event)
    {
        $this->event = $event;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => 'noreply@artevue.co.uk', 'name' => 'Artevue'])
                    ->subject('You have a potential buyer for your artwork!')
                    ->markdown('mails.buy_post_request_email');
    }
}
