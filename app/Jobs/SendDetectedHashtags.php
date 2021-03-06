<?php

namespace App\Jobs;

use App\User;
use Vision\Image;
use Vision\Vision;
use Vision\Feature;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDetectedHashtags
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;
    
    protected $user;
    protected $filepath;
    protected $unique_key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $filepath, $unique_key)
    {
        $this->user = $user;
        $this->filepath = $filepath;
        $this->unique_key = $unique_key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api_key = config('app.google_api_key');
        $vision = new Vision($api_key, [new Feature(Feature::LABEL_DETECTION, 10)]);

        $response = $vision->request(
            new Image($this->filepath)
        );
        $labels = $response->getLabelAnnotations();

        $hashtags = [];
        $i = 0;
        foreach ($labels as $label) {
            $hashtag = strtolower(str_replace(' ', '', $label->getDescription()));
            if (strlen($hashtag) <= 15 && $i < 8) {
                $hashtags[] = $this->parseHashtag($hashtag);
                $i += 1;
            }
        }
        array_unique($hashtags);
        unlink($this->filepath);

        $data = [
            'key' => $this->unique_key,
            'hashtags' => $hashtags
        ];

        $this->sendPusherNotification('User-'.$this->user->id,'hashtags-detected', $data);
    }

    /**
     * removes all symbols and spaces from a hashtag - only keeps characters and numbers
     * @param  [type] $hashtag [description]
     * @return [type]          [description]
     */
    public function parseHashtag($hashtag)
    {
        return preg_replace('/[^0-9a-zA-Z_]/', "", $hashtag);
    }
}
