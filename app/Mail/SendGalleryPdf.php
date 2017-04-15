<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendGalleryPdf extends Mailable
{
    protected $data;
    protected $user;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, User $user)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;
        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadView('pdf.gallery', compact('data'));
        $pdf->setPaper('a4')->setOption('margin-bottom', '0mm');

        $attachment = $pdf->inline();

        return $this->from('noreply@artevue.co.uk')
                    ->markdown('mails.sendGalleryPdf')
                    ->subject("Your ArteVue Gallery PDF Catalogue")
                    ->with([
                        'user' => $this->user
                    ])
                    ->attachData($attachment, 'gallery-pdf.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
