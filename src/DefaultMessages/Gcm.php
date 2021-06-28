<?php


namespace Imageplus\Sns\DefaultMessages;

/**
 * Default message for gcm
 * Class Gcm
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
class Gcm extends BaseMessage
{
    /**
     * Name is gcm
     * @var string
     */
    public static $name = 'GCM';

    /**
     * Default contents for gcm
     * @param  string $title
     * @param  string $message
     * @param  string $type
     * @param  array  $data
     * @return array
     */
    public static function getContents(string $title, string $message, string $type, array $data = []): array
    {
        //TODO: Shouldn't message be body?
        //https://firebase.google.com/docs/cloud-messaging/http-server-ref
        return [
            //uses data as a base
            'data' => [
                'message' => $message,
                'title'   => $title,
                'type'    => $type,
                'data'    => $data,
            ]
        ];
    }
}
