<?php


namespace Imageplus\Sns\InteractionHandlers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Imageplus\Sns\Contracts\SnsEndpointContract;
use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Contracts\SnsTopicSubscriptionContract;
use Imageplus\Sns\Exceptions\TopicDoesNotExistException;
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
     * @param  Model               $model
     * @param  SnsEndpointContract $endpoint
     * @return Collection<SnsTopicSubscriptionResource>|false
     */
    public function getSubscriptions(Model $model, SnsEndpointContract $endpoint){

        //developer defined callback to assign topics and attributes to an endpoint
        $topics = Sns::getEndpointCallback()($model, $endpoint);

        //if we don't have a callback we don't assign topics or attributes
        if($topics === false){
           return false;
        }

        return Collection::make($topics)
                        ->map(function(Array $attributes, string $topic) use($model, $endpoint){
                            //topic can be given as either an id, a name or a topic_arn
                            //priorities: 1: id, 2: topic_arn, 3: name
                            //if numeric it must be an id. Name and topic should never be numeric
                            if(is_numeric($topic)){
                                $topicModel = config('sns.models.topic')::find($topic);
                            } else {
                                $topicModel = config('sns.models.topic')::findByTopic($topic);

                                if($topicModel->exists()){
                                    $topicModel = $topicModel->first();
                                } else {
                                    $topicModel = config('sns.models.topic')::findByName($topic)->first();
                                }
                            }

                            if(!$topicModel){
                                throw new TopicDoesNotExistException("Topic {$topic} Does Not Exist");
                            }

                            //if we have an existing subscription for the device topic combination use that rather than create a new one
                            $existingSubscription = $model->sns_subscriptions()
                                                            ->forEndpoint($endpoint->id)
                                                            ->whereHas('topic', function($query) use($topicModel){
                                                                return $query->where('id', $topicModel->id);
                                                            })
                                                            ->first();

                            $subscription = $existingSubscription ?? $this->createSubscription($model, $topicModel, $endpoint);

                            //adds the attributes to the given subscription
                            $this->attachAttributes($subscription, $attributes);

                            return $subscription;
                        })
                        ->values();
    }

    /**
     * Creates the subscription in sns and saves the record
     * @param SnsTopicContract $topic
     * @param SnsEndpointContract $endpoint
     * @return SnsTopicSubscriptionContract
     */
    protected function createSubscription(Model $model, SnsTopicContract $topic, SnsEndpointContract $endpoint){
        //handles sns subscription
        $subscription = $this->subscribe($topic, $endpoint);

        //save subscription in the database
        return $model->sns_subscriptions()->create([
            //aws result object so use it to get the arn
            'subscription_arn' => $subscription->get('SubscriptionArn'),
            'sns_endpoint_id'  => $endpoint->id,
            'sns_topic_id'     => $topic->id
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

        //NOTES: SNS has its own exception
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

        //NOTES: SNS has its own exception
        //TODO: ADD EXCEPTION FOR FAILURE

        //delete the subscription record
        return $subscription->delete();
    }

    protected function attachAttributes(SnsTopicSubscriptionContract $subscription, Array $attributes){
        Sns::getClient()->setSubscriptionAttributes([
            'SubscriptionArn' => $subscription->subscription_arn,
            'AttributeName'   => 'FilterPolicy',
            'AttributeValue'  => json_encode($attributes)
        ]);

        //NOTES: SNS has its own exception
        //TODO: ADD EXCEPTION FOR FAILURE
    }
}
