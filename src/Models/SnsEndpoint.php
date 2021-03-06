<?php

namespace Imageplus\Sns\Models;

use Imageplus\Sns\Contracts\SnsEndpointContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnsEndpoint extends Model implements SnsEndpointContract
{
    protected $fillable = [
        'platform',
        'endpoint_arn',
        'device_token',
        'user_agent'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('sns.tables.endpoint'));
    }

    public function scopeForDevice($query, $device_token, $platform): Builder{
        return $query->where('device_token', $device_token)
                    ->where('platform', $platform);
    }

    public function subscription(): BelongsTo{
        return $this->belongsTo(config('sns.models.subscription'), 'id', 'sns_endpoint_id');
    }
}
