<?php


namespace Imageplus\Sns\InteractionHandlers;

use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Facades\Sns;

/**
 * Maps an endpoint to a topic
 * Class SnsTaggingHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsTaggingHandler
{

    /**
     * Attaches a custom tag to a topic
     * @param SnsTopicContract $topic
     */
    public function createTags(SnsTopicContract $topic)
    {
        return Sns::getClient()->tagResource([
            'ResourceArn' => $topic->topic_arn,
            'Tags' => config('sns.topic_tags')
        ]);
    }
}
