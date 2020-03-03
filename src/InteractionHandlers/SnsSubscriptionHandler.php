<?php


namespace Imageplus\Sns\InteractionHandlers;

use Imageplus\Sns\Contracts\SnsEndpointContract;
use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Contracts\SnsTopicSubscriptionContract;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\Traits\instancesSnsModels;
use Aws\Result;

/**
 * Maps an endpoint to a topic
 * Class SnsSubscriptionHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsSubscriptionHandler
{
    use instancesSnsModels;

    /**
     * Gets a subscription by finding it or creating it
     * @param SnsTopicContract $topic
     * @param SnsEndpointContract $endpoint
     * @return SnsTopicSubscriptionContract
     */
    public function getSubscription(SnsTopicContract $topic, SnsEndpointContract $endpoint){
        //builds query for finding endpoint
        $subscription = $topic->subscriptions()->forEndpoint($endpoint->id);

        //if the endpoint exists return it otherwise create and return one
        return
            $subscription->first() ?? $this->createSubscription($topic, $endpoint);
    }

    /**
     * Creates the subscription in sns and saves the record
     * @param SnsTopicContract $topic
     * @param SnsEndpointContract $endpoint
     * @return SnsTopicSubscriptionContract
     */
    protected function createSubscription(SnsTopicContract $topic, SnsEndpointContract $endpoint){
        //handles sns subscription
        $subscription = $this->subscribe($topic, $endpoint);

        //save subscription in the database
        return $topic->subscriptions()->create([
            //aws result object so use it to get the arn
            'subscription_arn' => $subscription->get('SubscriptionArn'),
            'sns_endpoint_id' => $endpoint->id,
        ]);
    }

    /**
     * Handles creating the subscription in sns
     * @param SnsTopicContract $topic
     * @param SnsEndpointContract $endpoint
     * @return Result
     */
    protected function subscribe(SnsTopicContract $topic, SnsEndpointContract $endpoint){
        //return the subscription created
        return Sns::getClient()->subscribe([
            'Endpoint' => $endpoint->endpoint_arn,
            'Protocol' => 'application',
            'TopicArn' => $topic->topic_arn
        ]);

        //TODO: ADD EXCEPTION FOR FAILURE
    }

    /**
     * Removes the subscription from sns and deleted the record
     * @param SnsTopicSubscriptionContract $subscription
     * @return bool
     */
    public function removeSubscription(SnsTopicSubscriptionContract $subscription){
        //remove the subscription from sns
        Sns::getClient()->unsubscribe([
            'SubscriptionArn' => $subscription->subscription_arn
        ]);

        //TODO: ADD EXCEPTION FOR FAILURE

        //delete the subscription record
        return $subscription->delete();
    }
}
