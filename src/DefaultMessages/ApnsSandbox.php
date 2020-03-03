<?php


namespace Imageplus\Sns\src\DefaultMessages;

/**
 * This uses the same format as Apns but with a different name
 * so this extends apns and changes the name
 * Class ApnsSandbox
 * @package Imageplus\Sns\DefaultMessages
 * @author Harry Hindson
 */
class ApnsSandbox extends Apns
{
    public $name = 'APNS_SANDBOX';
}
