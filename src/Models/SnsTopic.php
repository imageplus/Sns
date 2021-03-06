<?php

namespace Imageplus\Sns\Models;

use Imageplus\Sns\Contracts\SnsTopicContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnsTopic extends Model implements SnsTopicContract
{
    protected $fillable = [
        'topic_arn',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('sns.tables.topic'));
    }

    public function scopeFindTopic($query, $topic): Builder{
        return $query->where('topic_arn', $topic);
    }

    public function model(){
        return $this->morphTo();
    }

    public function subscriptions(): HasMany{
        return $this->hasMany(config('sns.models.subscription'));
    }
}
