<?php


namespace Imageplus\Sns\DefaultMessages;


use Imageplus\Sns\Contracts\SnsDefaultApnMessageContract;

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
    public static $name;

    /**
     * Gets the name of the type of message (used to key the array)
     * @return string
     */
    public static function getName(): string
    {
       return static::$name;
    }

    /**
     * Gets the array form of the contents of the default message
     * @param  string $title
     * @param  string $message
     * @param  string $type
     * @param  array  $data
     * @return array
     */
    public static function getContents(string $title, string $message, string $type, array $data = []): array
    {
        return [
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'data'    => $data
        ];
    }
}
