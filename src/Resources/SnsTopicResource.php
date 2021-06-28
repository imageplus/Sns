<?php

namespace Imageplus\Sns\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SnsTopicResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'topic_arn' => $this->topic_arn
        ];
    }
}
