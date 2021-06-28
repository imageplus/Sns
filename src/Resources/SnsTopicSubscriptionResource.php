<?php

namespace Imageplus\Sns\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SnsTopicSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'subscription_arn' => $this->subscription_arn,
            'sns_topic'        => SnsTopicResource::make($this->topic),
        ];
    }
}
