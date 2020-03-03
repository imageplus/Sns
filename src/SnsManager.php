<?php


namespace Imageplus\Sns\src;


use Imageplus\Sns\src\Contracts\SnsTopicContract;
use Imageplus\Sns\src\Contracts\SnsTopicSubscriptionContract;
use Imageplus\Sns\src\InteractionHandlers\SnsEndpointHandler;
use Imageplus\Sns\src\InteractionHandlers\SnsMessageHandler;
use Imageplus\Sns\src\InteractionHandlers\SnsSubscriptionHandler;
use Imageplus\Sns\src\InteractionHandlers\SnsTopicHandler;
use Aws\Sns\SnsClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

/**
 * Class SnsManager
 * @package Imageplus\Sns
 * @author Harry Hindson
 */
class SnsManager
{
    protected $errors = [];
    /**
     * As this class is used as a singleton sav the sns client
     * @var SnsClient
     */
    protected $client;

    /**
     * Contains all of the different aspects of sns this class deals with
     * @var string[]
     */
    protected $handlers = [
        'topic' => SnsTopicHandler::class,
        'endpoint' => SnsEndpointHandler::class,
        'subscription' => SnsSubscriptionHandler::class,
        'message' => SnsMessageHandler::class
    ];

    /**
     * Sns constructor.
     * Whenever the class is used a client is required
     */
    public function __construct(){
        $this->client = $this->createClient();

        //this will instantiate all handlers related to this class
        $this->initialiseHandlers();
    }

    /**
     * Initialises all handlers used and stored them back where they were
     * @return void
     */
    protected function initialiseHandlers(){
        foreach($this->handlers as $key=>$handler){
            //initialise the current handler and replace its class in the array with that
            $this->handlers[$key] = new $handler();
        }
    }

    /**
     * Logs in to AWS SNS
     * @return SnsClient
     */
    protected function createClient(){
        //Generate an sns client from the credentials stored in the config file
        return new SnsClient([
            'credentials' => [
                'key' => config('sns.credentials.key'),
                'secret' => config('sns.credentials.secret'),
            ],
            'region'  => config('sns.region'),
            'version'  => config('sns.version')
        ]);
    }

    /**
     * Generates routes for the package
     * @param array $attributes
     */
    public function routes($attributes = []){
        //so I can use this as attributes on a route group apply the defaults here
        $attributes = array_merge([
            'prefix' => '/sns',
            'middleware' => ['auth:api'],
            'as' => 'sns.'
        ], $attributes);

        //create a group of routes for this with all the default values
        Route::group($attributes, function(){
            //route to add a new subscription
            Route::post('/add_device/{model_id}', 'Imageplus\Sns\Controllers\SnsController@addDevice')->name('add_device');

            //route to remove a subscription
            Route::delete('/remove_device/{value}', 'Imageplus\Sns\Controllers\SnsController@removeDevice')->name('remove_device');

            //route to remove a topic
            Route::delete('/remove_topic/{value}', 'Imageplus\Sns\Controllers\SnsController@removeTopic')->name('remove_topic');
        });
    }

    /**
     * Even though this is accessed through a facade and is a singleton
     * its useful to still have access to its instance for properties
     * @return $this
     */
    public function instance(){
        return $this;
    }

    /**
     * The client is used in the handlers so made it accessible through a getter
     * @return SnsClient
     */
    public function getClient(){
        return $this->client;
    }

    public function getErrors(){
        return $this->errors;
    }

    /**
     * Gets the subscription for a device by returning or creating it
     * @param Model $model
     * @param $device_token
     * @param $platform
     * @param bool $forceReset
     * @return SnsTopicSubscriptionContract|bool
     */
    public function registerDevice(Model $model, $device_token, $platform, $forceReset = false){

        //reset errors as this is a new request
        $this->errors = [];

        //Recreates the device if it needs too
        if($forceReset){
            //can only unregister if an endpoint exists
            $endpoint = $this->handlers['endpoint']->findEndpoint($device_token, $platform);
            if($endpoint){
                //call the unregister method with the subscription
                $this->unregisterDevice($endpoint->subscription);
            }
        }

        //get or create the endpoint (should be 1 per device)
        $endpoint = $this->handlers['endpoint']->getEndpoint($platform, $device_token);

        if(!$endpoint){
            $this->errors = $this->handlers['endpoint']->getErrors();
            return false;
        }

        //will create a subscription or return it
        //(should be 1 per device as it maps an endpoint to a topic)
        return $this->handlers['subscription']
            ->getSubscription(
                //get or create the topic (should be 1 per user)
                $this->handlers['topic']->getTopic($model),
                $endpoint
            );
    }

    /**
     * @param String|SnsTopicSubscriptionContract $value
     * @return bool
     */
    public function unregisterDevice($value){
        //if the value implements the SnsTopicSubscriptionContract use it otherwise find it from the value
        $subscription = is_a($value, $this->handlers['subscription']->model)
                            ? $value
                            : $this->handlers['subscription']->model::findSubscription($value)->first();

        //TODO: ADD ERROR HANDLING INCASE SUBSCRIPTION DOESN'T EXIST

        //remove both the endpoint and the subscription
        $this->handlers['subscription']->removeSubscription($subscription);
        $this->handlers['endpoint']->removeEndpoint($subscription->endpoint);

        return true;
    }

    /**
     * Will unregister a topic (remove all model related data)
     * @param String|SnsTopicContract $value
     * @return bool
     */
    public function unregisterTopic($value){
        //if the value implements the SnsTopicContract use it otherwise find it from the value
        $topic = is_a($value, $this->handlers['topic']->model)
                    ? $value
                    : $this->handlers['topic']->model::findTopic($value)->first();

        //TODO: ADD ERROR HANDLING INCASE TOPIC DOESN'T EXIST

        //detatch all subscriptions relating to the topic
        $topic->subscriptions->each(function($subscription){
            $this->unregisterDevice($subscription);
        });

        //remove the topic itself
        $this->handlers['topic']->removeTopic($topic);

        return true;
    }

    /**
     * Gets the base of the message handler
     * @return SnsMessageHandler
     */
    public function message(){
        return $this->handlers['message'];
    }
}
