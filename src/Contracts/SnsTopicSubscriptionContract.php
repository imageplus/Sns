<?php


namespace Imageplus\Sns\Contracts;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface SnsTopicSubscriptionContract
 * @package Imageplus\Sns\Contracts
 * @author Harry Hindson
 */
interface SnsTopicSubscriptionContract
{
    /**
     * Gets the endpoint attached to the subscription
     * @return HasOne
     */
    public function endpoint(): HasOne;

    /**
     * Gets the topic the subscription was attached to
     * @return BelongsTo
     */
    public function topic(): BelongsTo;

    /**
     * Gets the model attached to the subscription
     * @return MorphTo
     */
    public function model(): MorphTo;

    /**
     * Query builder which finds the subscription for an endpoint
     * @param $query
     * @param $endpoint
     * @return Builder
     */
    public function scopeForEndpoint($query, $endpoint): Builder;

    /**
     * Finds a subscription from its arn
     * @param $query
     * @param $subscription_arn
     * @return Builder
     */
    public function scopeFindSubscription($query, $subscription_arn): Builder;
}
