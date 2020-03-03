<?php
return [
    /**
     * All of the credentials used by the sns handler
     */
    'credentials' => [
        'key'    => env('SNS_KEY', ''),
        'secret' => env('SNS_SECRET', ''),
    ],

    /**
     * Region the sns account is using
    */
    'region' => env('SNS_REGION', 'eu-west-1'),

    /**
     * Version of sns to use
    */
    'version' => env('SNS_VERSION', 'latest'),

    /**
     * Whenever making a topic this value is used as a prefix then followed by the model and the models id
     */
    'topic_prefix' => env('SNS_TOPIC_PREFIX', env('APP_NAME')),

    /**
     * This controls the ARNS of all possible device types as they require different arns
     * to be able to create subscriptions within sns
     */
    'platform_arns' => [
        'android' => env('ANDROID_PLATFORM_ARN', ''),
        'ios' => env('IOS_PLATFORM_ARN', ''),
    ],

    /**
     * Holds all of the models used by the sns manager
     */
    'models' => [
        /**
         * This stores the topic arns created by the sns manager
         * Feel free to change this as long as it implements
         * the SnsTopicContract.
         * The SnsTopicHandler will pickup the change
         */
        'topic' => \Imageplus\Sns\Models\SnsTopic::class,

        /**
         * This stores the endpoint arns created by the sns manager
         * Feel free to change this as long as it implements
         * the SnsEndpointContract.
         * The SnsEndpointHandler will pickup the change
         */
        'endpoint' => \Imageplus\Sns\Models\SnsEndpoint::class,

        /**
         * This stores the subscription arns created by the sns manager
         * Feel free to change this as long as it implements
         * the SnsTopicSubscriptionContract.
         * The SnsSubscriptionHandler will pickup the change
         */
        'subscription' => \Imageplus\Sns\Models\SnsTopicSubscription::class
    ],

    /**
     * Holds the tables used by the models within the sns handler
     */
    'tables' => [

        /**
         * This is the table name used for the sns topics
         * This can be changed and will be reference in the
         * SnsTopic model
         */
        'topic' => 'sns_topics',

        /**
         * This is the table name used for the sns endpoints
         * This can be changed and will be reference in the
         * SnsEndpoint model
         */
        'endpoint' => 'sns_endpoints',

        /**
         * This is the table name used for the sns subscriptions
         * This can be changed and will be reference in the
         * SnsTopicSubscription model
         */
        'subscription' => 'sns_topic_subscriptions'
    ],

    /**
     * Holds all of the messages to send when using useDefault method
     * You can add and remove these as required but these should work
     * for most default cases
     */
    'default_messages' => [
        /**
         * This is the default message for IOS
         */
        \Imageplus\Sns\DefaultMessages\Apns::class,
        /**
         * This is the same as Apns with a different name
         */
        \Imageplus\Sns\DefaultMessages\ApnsSandbox::class,
        /**
         * Default message for ANDROID
         */
        \Imageplus\Sns\DefaultMessages\Gcm::class
    ],

    /**
     * Default Model is used only if you use the controller provided by the package
     * It will use this model to attach subscriptions to
     */
    'default_model' => \App\User::class,

    /**
     * Route uris used by the package
     * Feel free to change these
     */
    'routes' => [
        /**
         * route used to register a device
         */
        'register' => 'registerDevice',

        /**
         * Route used to remove a device
         */
        'unregister' => 'unregisterDevice',

        /**
         * Route used to unregister a topic
         */
        'remove_topic' => 'unregisterTopic'
    ],

    /**
     * This is used in the register method as the model to use if the model_id is not passed in
     * the parameter on the route will no longer be optional if this is set to false
     */
    'use_auth' => true
];
