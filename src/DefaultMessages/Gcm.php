<?php


namespace Imageplus\Sns\src\DefaultMessages;

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
    public $name = 'GCM';

    /**
     * Default contents for gcm
     * @param string $title
     * @param string $message
     * @param string $type
     * @param $data
     * @return array|array[]
     */
    public function getContents(string $title, string $message, string $type, $data): array
    {
        return [
            //uses data as a base
            'data' => [
                'message' => $message,
                'title' => $title,
                'type' => $type,
                'data' => $data,
            ]
        ];
    }
}
