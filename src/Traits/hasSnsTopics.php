<?php


namespace Imageplus\Sns\Traits;


use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait hasSnsTopics
 * @package Imageplus\Sns\Traits
 * @author Harry Hindson
 */
trait hasSnsTopics
{
    /**
     * Gets the sns_topic from the default model
     * @return MorphOne
     */
    public function sns_topic(){
        return $this->morphOne(config('sns.models.topic'), 'model');
    }
}
