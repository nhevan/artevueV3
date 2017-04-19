<?php

namespace Acme\Transformers;


/**
*
*/
class MessageTransformer extends Transformer
{
    public function transform($message)
    {	
        return [
                'id' => $message['id'],
                'sender_id' => $message['sender_id'],
                'receiver_id' => $message['receiver_id'],
                'message' => $message['message'],
                'is_read' => $message['is_read'],
                'is_file' => $message['is_file'],
                'is_post' => $message['is_post'],
                'url' => $message['url'],
                'created_at' => $message['created_at'],
                'updated_at' => $message['updated_at'],
            ];
    }
}