<?php


namespace Imageplus\Sns\src\DefaultMessages;

/**
 * Default Apns Message
 * Class Apns
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
class Apns extends BaseMessage
{
    /**
     * Set the name to APNS
     * @var string
     */
    public $name = 'APNS';

    /**
     * Builds the default apns message contents
     * @param string $title
     * @param string $message
     * @param string $type
     * @param $data
     * @return array|array[]
     */
    public function getContents(string $title, string $message, string $type, $data): array
    {
        return [
            //aps is the default body for apples sns messages
            'aps' => [
                'sound' => 'default',
                'type' => $type,
                'alert' => [
                    'title' => $title,
                    'body' => $message
                ],
                'data' => $data,
            ]
        ];
    }
}
