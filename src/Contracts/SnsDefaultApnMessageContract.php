<?php


namespace Imageplus\Sns\Contracts;

/**
 * Used for generating the body of default messages
 * Interface SnsDefaultApnMessageContract
 * @package Imageplus\Sns\Contracts
 * @author Harry Hindson
 */
interface SnsDefaultApnMessageContract
{
    //gets the name/key of the message
    public function getName(): string;

    //gets the message contents
    public function getContents(string $title, string $message, string $type, $extra): array;
}
