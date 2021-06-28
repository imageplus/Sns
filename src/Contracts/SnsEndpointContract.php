<?php


namespace Imageplus\Sns\Contracts;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interface SnsEndpointContract
 * @package Imageplus\Sns\Contracts
 * @author Harry Hindson
 */
interface SnsEndpointContract
{
    /**
     * Used to find the record by the device
     * @param $query
     * @param $device_token
     * @param $platform
     * @return Builder
     */
    public function scopeForDevice($query, $device_token, $platform): Builder;

    /**
     * Finds the endpoints subscription
     * this can be null
     * @return HasMany
     */
    public function subscriptions(): HasMany;
}
