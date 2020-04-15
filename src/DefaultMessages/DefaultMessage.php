<?php


namespace Imageplus\Sns\src\DefaultMessages;

/**
 * Default Apns Message
 * Class Apns
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
class DefaultMessage extends BaseMessage
{
    /**
     * Set the name to APNS
     * @var string
     */
    public $name = 'default';

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
            'data' => [
                'message' => $message,
                'title' => $title,
                'type' => $type,
                'data' => $data,
            ]
        ];
    }
}
