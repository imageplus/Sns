<?php

namespace Imageplus\Sns\Models;

use Imageplus\Sns\Contracts\SnsTopicSubscriptionContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SnsTopicSubscription extends Model implements SnsTopicSubscriptionContract
{
    protected $fillable = [
        'subscription_arn',
        'sns_endpoint_id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('sns.tables.subscription'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endpoint(): HasOne{
        return $this->hasOne(config('sns.models.endpoint'), 'id', 'sns_endpoint_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic(): BelongsTo{
        return $this->belongsTo(config('sns.models.topic'), 'sns_topic_id', 'id');
    }

    /**
     * @param $query
     * @param $endpoint
     * @return Builder
     */
    public function scopeForEndpoint($query, $endpoint): Builder{
        return $query->where('sns_endpoint_id', $endpoint);
    }

    /**
     * @param $query
     * @param $subscription_arn
     * @return Builder
     */
    public function scopeFindSubscription($query, $subscription_arn): Builder {
        return $query->where('subscription_arn', $subscription_arn);
    }

}
