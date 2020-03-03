<?php


namespace Imageplus\Sns\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class Sns
 * @package Imageplus\Sns\Facades
 * @author Harry Hindson
 */
class Sns extends Facade
{
    public static function getFacadeAccessor()
    {
        //sns manager is the singleton instance
        return 'sns_manager';
    }
}
