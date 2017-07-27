<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPasswordEmail extends Mailable
{
    protected $user;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $new_password = str_random(8);
        $this->user->password = bcrypt($new_password);
        $username = $this->user->username;

        $this->user->save();

        return $this->from('noreply@artevue.co.uk')
                    ->markdown('mails.newPasswordEmail')
                    ->subject('Forgot password email')
                    ->with([
                        'new_password' => $new_password,
                        'username' => $username
                    ]);
    }
}
