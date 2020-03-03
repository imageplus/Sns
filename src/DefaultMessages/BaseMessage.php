<?php


namespace Imageplus\Sns\src\DefaultMessages;


use Imageplus\Sns\src\Contracts\SnsDefaultApnMessageContract;

/**
 * Base class for default messages
 * Class BaseMessage
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
abstract class BaseMessage implements SnsDefaultApnMessageContract
{
    /**
     * Used to hold the name of the type
     * @var string
     */
    public $name;

    /**
     * Gets the name of the type of message (used to key the array)
     * @return string
     */
    public function getName(): string
    {
       return $this->name;
    }

    /**
     * Gets the array form of the contents of the default message
     * @param string $title
     * @param string $message
     * @param string $type
     * @param $data
     * @return array
     */
    public function getContents(string $title, string $message, string $type, $data): array
    {
        return [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ];
    }
}
