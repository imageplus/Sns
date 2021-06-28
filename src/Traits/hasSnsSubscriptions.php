<?php


namespace Imageplus\Sns\Traits;


use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait hasSnsTopics
 * @package Imageplus\Sns\Traits
 * @author Harry Hindson
 */
trait hasSnsSubscriptions
{
    /**
     * Gets the sns_topic from the default model
     * @return MorphMany
     */
    public function sns_subscriptions(){
        return $this->morphMany(config('sns.models.subscription'), 'model');
    }
}
