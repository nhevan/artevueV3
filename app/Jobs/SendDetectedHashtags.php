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

class SendDetectedHashtags implements ShouldQueue
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
        $vision = new Vision($api_key, [new Feature(Feature::WEB_DETECTION, 10)]);
        $response = $vision->request(
            new Image($this->filepath)
        );


        $hashtags = [];
        $i = 0;
        $webDetection = $response->getWebDetection();
        foreach ($webDetection->getWebEntities() as $entity) {
            //if ($entity->getScore() > 0.6) {
                $hashtag = strtolower(str_replace(' ', '', $entity->getDescription()));
                if (strlen($hashtag) <= 15 && $i < 8) {
                    $hashtags[] = $hashtag;
                    $i += 1;
                }
            //}
        }
        array_unique($hashtags);
        unlink($this->filepath);

        $data = [
            'key' => $this->unique_key,
            'hashtags' => $hashtags
        ];
        
        $this->sendPusherNotification($this->user->id.'-personal-channel','hashtags-detected', $data);
    }
}
