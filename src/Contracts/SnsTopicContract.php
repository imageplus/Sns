<?php


namespace Imageplus\Sns\Contracts;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interface SnsTopicContract
 * @package Imageplus\Sns\Contracts
 * @author Harry Hindson
 */
interface SnsTopicContract
{
    /**
     * Finds an sns topic from the arn
     * @param $query
     * @param $topic
     * @return Builder
     */
    public function scopeFindTopic($query, $topic): Builder;

    /**
     * Finds all subscriptions attached to the topic
     * @return HasMany
     */
    public function subscriptions(): HasMany;
}
