<?php


namespace Imageplus\Sns;


use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Database\Eloquent\Collection;
use Imageplus\Sns\Contracts\SnsEndpointContract;
use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Contracts\SnsTopicSubscriptionContract;
use Imageplus\Sns\Exceptions\EndpointDoesNotExistException;
use Imageplus\Sns\Exceptions\InvalidCallbackException;
use Imageplus\Sns\Exceptions\SubscriptionDoesNotExistException;
use Imageplus\Sns\Exceptions\TopicDoesNotExistException;
use Imageplus\Sns\InteractionHandlers\SnsEndpointHandler;
use Imageplus\Sns\InteractionHandlers\SnsMessageHandler;
use Imageplus\Sns\InteractionHandlers\SnsSubscriptionHandler;
use Imageplus\Sns\InteractionHandlers\SnsTopicHandler;
use Aws\Sns\SnsClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Imageplus\Sns\InteractionHandlers\SnsTaggingHandler;

/**
 * Class SnsManager
 * @package Imageplus\Sns
 * @author Harry Hindson
 */
class SnsManager
{
    /**
     * @var MessageBag|null
     */
    protected $errors = null;

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
        'topic'        => SnsTopicHandler::class,
        'tagging'      => SnsTaggingHandler::class,
        'endpoint'     => SnsEndpointHandler::class,
        'subscription' => SnsSubscriptionHandler::class,
        'message'      => SnsMessageHandler::class
    ];

    /**
     * Contains a callback to generate the array of topics and attributes to assign to an endpoint
     * @var callable|false
     */
    protected $endpointSubscriptionCallback = false;

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
    protected function createClient(): SnsClient
    {
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
    public function routes(array $attributes = []){
        //so I can use this as attributes on a route group apply the defaults here
        $attributes = array_merge([
            'prefix' => '/sns',
            'middleware' => ['auth:api'],
            'as' => 'sns.'
        ], $attributes);

        //create a group of routes for this with all the default values
        Route::group($attributes, function(){
            //route to add a new subscription
            //the model_id parameter is only optional when the use_auth config is true
            Route::post(config('sns.routes.register') . '/{model_id' . (config('sns.use_auth') ? '?' : '') .'}', 'Imageplus\Sns\Controllers\SnsController@registerDevice')->name('register-device');

            //route to remove a subscription
            //post as we need data
            Route::post(config('sns.routes.unregister'), 'Imageplus\Sns\Controllers\SnsController@unregisterDevice')->name('unregister-device');
        });
    }

    /**
     * Even though this is accessed through a facade and is a singleton
     * its useful to still have access to its instance for properties
     * @return $this
     */
    public function instance(): SnsManager
    {
        return $this;
    }

    /**
     * The client is used in the handlers so made it accessible through a getter
     * @return SnsClient
     */
    public function getClient(): SnsClient
    {
        return $this->client;
    }

    /**
     * Gets any errors assigned back to this from a handler
     * @return MessageBag|null
     */
    public function getErrors(){
        return $this->errors;
    }

    /**
     * Adds a callback to define the topics and attributes an endpoint will be subscribed to
     * @param   callable|false $callback
     * @returns bool
     * @throws  InvalidCallbackException
     */
    public function assignEndpointToTopics($callback = false): bool
    {
        if(is_callable($callback) || $callback === false){
            $this->endpointSubscriptionCallback = $callback;

            return true;
        }

        throw new InvalidCallbackException('Callback given must be a valid callback or false to remove the current callback');
    }

    /**
     * Gets the user defined callback for generating topics and attributes
     * @return callable|false
     */
    public function getEndpointCallback(){
        return $this->endpointSubscriptionCallback;
    }

    /**
     * Finds or creates a new topic
     * @param String $name
     * @return SnsTopicContract|bool
     */
    public function findOrCreateTopic(string $name){
        //gets a new or existing topic as a model instance
        $topic = $this->handlers['topic']->getTopic($name);

        if(!$topic){
            return false;
        }

        //if the topic is an instance of the current model
        if(is_a($topic, $this->handlers['topic']->model)){
            //if tags have been defined call the method to assign them to the topic
            if(config('sns.topic_tags', false) !== false){
                $this->handlers['tagging']->createTags($topic);
            }
        }

        return $topic;
    }

    /**
     * Gets the subscription for a device by returning or creating it
     * @param  Model $model
     * @param  string $device_token
     * @param  string $platform
     * @param  bool $forceReset
     * @return SnsTopicSubscriptionContract[]|bool
     */
    public function registerDevice(Model $model, string $device_token, string $platform, bool $forceReset = false){

        //reset errors as this is a new request
        $this->errors = [];

        //if force reset is true this will remove all subscriptions and the endpoint for the current device token/platform
        $this->handlesExistingEndpoint($model, $device_token, $platform, $forceReset);

        //Adds the new endpoints
        $endpoint = $this->handlesEndpoints($device_token, $platform);

        //this validates so if its false it failed validation
        if(!$endpoint){
            return false;
        }

        //this removes subscriptions from the endpoint for a different model
        //only 1 user can be registered to 1 device
        $this->handlesExistingSubscriptions($endpoint, $model);

        //Adds the new subscriptions to the endpoint/device
        return $this->handlesSubscriptions($model, $endpoint);
    }

    /**
     * @param Model $model
     * @param String $device_token
     * @param String $platform
     * @param bool $forceReset
     */
    protected function handlesExistingEndpoint(Model $model, string $device_token, string $platform, bool $forceReset){
        //Recreates the device if it needs too
        if($forceReset){
            //can only unregister if an endpoint exists
            $endpoint = $this->handlers['endpoint']->findEndpoint($device_token, $platform);

            if($endpoint){

                //removes all existing subscriptions for the endpoint
                $this->handlesExistingSubscriptions($endpoint, $model, true);

                //call the unregister method with the subscription
                $this->unregisterDevice($endpoint->subscription);
            }
        }
    }

    /**
     * attempts to find or create an endpoint
     * @param  string $device_token
     * @param  string $platform
     * @return SnsEndpointContract|false
     */
    protected function handlesEndpoints(string $device_token, string $platform){
        //get or create the endpoint (should be 1 per device)
        $endpoint = $this->handlers['endpoint']->getEndpoint($platform, $device_token);

        if(!$endpoint){
            $this->errors = $this->handlers['endpoint']->getErrors();
            return false;
        }

        return $endpoint;
    }

    /**
     * removes all subscriptions from sns
     * if forceReset is true all subscriptions will be removed from the endpoint no matter what
     * otherwise they'll be removed if the model key is different to the subscription key
     * @param SnsEndpointContract $endpoint
     * @param Model|null $model
     * @param bool $forceReset
     */
    protected function handlesExistingSubscriptions(SnsEndpointContract $endpoint, Model $model = null, $forceReset = false){
        //If the endpoint found already has a subscription make sure
        //it is for the current user, and if not we remove the subscription
        //and continue as before
        if($endpoint->subscriptions()->count() > 0){
            $endpoint->subscriptions()->get()->each(function(SnsTopicSubscriptionContract $subscription) use($model, $forceReset){
                if((is_a($model, Model::class) && $subscription->model->getKey() != $model->getKey()) || $forceReset){
                    //The endpoint can remain intact as this will be reused for the device and just map subscriptions with the new model
                    $this->handlers['subscription']->removeSubscription($subscription);
                }
            });
        }
    }

    /**
     * Tries to find a subscription or attempts to create a new one
     * @param  Model $model
     * @param  SnsEndpointContract $endpoint
     * @return Collection<SnsTopicSubscriptionResource>|false
     */
    protected function handlesSubscriptions(Model $model, SnsEndpointContract $endpoint){
        //will create a subscription or return it
        //(should be 1 per device as it maps an endpoint to a topic)
        $subscriptions = $this->handlers['subscription']
                            ->getSubscriptions($model, $endpoint);

        if(!$subscriptions){
            //doesn't have validation errors only exceptions
//            $this->errors = $this->handlers['subscription']->getErrors();
            return false;
        }

        return $subscriptions;
    }

    /**
     * Unregisters a device using the endpoint
     * @param  array|SnsEndpointContract $value
     * @return bool
     * @throws EndpointDoesNotExistException
     */
    public function unregisterDevice($value): bool
    {
        $endpoint = is_a($value, $this->handlers['endpoint']->model)
                        ? $value
                        : $this->handlers['endpoint']->findEndpoint(...$value);

        if(!$endpoint){
            throw new EndpointDoesNotExistException('Value given cannot be mapped to a valid endpoint');
        }

        //force removal of all subscriptions from the endpoint
        $this->handlesExistingSubscriptions($endpoint, null, true);

        return $this->handlers['endpoint']->removeEndpoint($endpoint);
    }

    /**
     * Removes an existing subscription from sns
     * @param  string|SnsTopicSubscriptionContract $value
     * @return bool
     * @throws SubscriptionDoesNotExistException
     */
    public function unsubscribeFromTopic($value): bool
    {
        $subscription = is_a($value, $this->handlers['subscription']->model)
                ? $value
                : $this->handlers['subscription']->model::findSubscription($value)->first();

        if(!$subscription){
            throw new SubscriptionDoesNotExistException("Value given cannot be mapped to a valid subscription");
        }

        return $this->handlers['subscription']->removeSubscription($subscription);
    }

    /**
     * Will unregister a topic and delete all subscriptions attached to it
     * @param  String|SnsTopicContract $value
     * @return bool
     * @throws TopicDoesNotExistException
     */
    public function unregisterTopic($value): bool
    {
        //if the value implements the SnsTopicContract use it otherwise find it from the value
        $topic = is_a($value, $this->handlers['topic']->model)
                    ? $value
                    : $this->handlers['topic']->model::findByTopic($value)->first();

        if(!$topic){
            throw new TopicDoesNotExistException('Topic given cannot be mapped to a valid topic');
        }

        //detatch all subscriptions relating to the topic
        $topic->subscriptions->each(function($subscription){
            $this->handlers['subscription']->removeSubscription($subscription);
        });

        //remove the topic itself
        $this->handlers['topic']->removeTopic($topic);

        return true;
    }

    /**
     * Gets the base of the message handler
     * @return SnsMessageHandler
     */
    public function message(): SnsMessageHandler
    {
        return $this->handlers['message'];
    }
}
