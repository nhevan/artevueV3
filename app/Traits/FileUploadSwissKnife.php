<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

trait FileUploadSwissKnife{
	/**
     * uploads a post image to Amazon s3
     * @return [type] [description]
     */
    public function uploadPostImageTos3($field_name, $folder_path)
    {
        $app_version = (float) strtolower(request()->header("X-ARTEVUE-App-Version"));

        if($app_version >= 2){
            $filepath = $this->makeFileFromBase64($this->request->$field_name);

            $path = Storage::disk('s3')->putFile($folder_path, new File($filepath));
            unlink($filepath);
        }else{
        	$path = $this->request->file($field_name)->store(
                $folder_path, 's3'
            );
        }
        
        return $path;
    }

    /**
     * creates a file from a given base 64 encoded string
     * @param  [type] $image_as_base64_string [description]
     * @return [type]                         [description]
     */
    protected function makeFileFromBase64($image_as_base64_string)
    {
        $encoded_image = $image_as_base64_string;
        $extension = explode('/', substr($encoded_image, 0, strpos($encoded_image, ';')))[1];

        if ($this->isAllowedExtension($extension)) {
            $base64 = explode(',', $encoded_image)[1];

            $filepath = storage_path('app/public')."/images/".uniqid().'.'.$extension;
            $decoded_image = base64_decode($base64);
            file_put_contents($filepath, $decoded_image);

            return $filepath;
        }

        throw new \Exception("only jpeg and jpg is allowed.");
    }

    /**
     * checks if the extension is a allowed extension
     * @param  [type]  $extension [description]
     * @return boolean            [description]
     */
    protected function isAllowedExtension($extension)
    {
        return in_array($extension, ['jpg', 'jpeg', 'png']);
    }
}

?>