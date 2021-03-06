<?php


namespace Imageplus\Sns\DefaultMessages;

/**
 * Default Message
 * Class DefaultMessage
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
class DefaultMessage extends BaseMessage
{
    /**
     * Set the name to default
     * @var string
     */
    public $name = 'default';

    /**
     * Builds the default message contents
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
