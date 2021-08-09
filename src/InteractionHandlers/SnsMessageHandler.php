<?php


namespace Imageplus\Sns\InteractionHandlers;


use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Arr;
use Imageplus\Sns\Contracts\SnsDefaultApnMessageContract;
use Imageplus\Sns\Exceptions\InvalidMessageTypeException;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\Traits\validatesObjects;

/**
 * Class SnsMessageHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsMessageHandler
{
    use validatesObjects;

//    /**
//     * Holds all portions of the message for the different types
//     * So they can be added separately then sent at once
//     * @var array
//     */
//    public $messages    = [];

    /**
     * Holds the title of the notification
     * @var string
     */
    protected $title       = '';

    /**
     * Holds the notification content
     * @var string
     */
    protected $content     = '';

    /**
     * Holds the current message type
     * defaults to `{APP NAME} Notifications`
     * @var string
     */
    protected $messageType = '';

    /**
     * Holds the additional data passed with the notifications
     * @var array
     */
    protected $additionalData = [];

    /**
     * Holds all of the device types we want to send messages to
     * @var SnsDefaultApnMessageContract|SnsDefaultApnMessageContract[]
     */
    protected $deviceTypes = [];

    /**
     * Holds all the attributes we need to validate against in SNS (Filter Policy)
     * @var array
     */
    protected $attributes  = [];

    /**
     * Sets all of the default data
     */
    public function __construct()
    {
        $this->deviceTypes = config('sns.default_messages');
        $this->messageType = config('app.name') . ' Notifications';
    }

    /**
     * Allows us to target an individual device type e.g. iOS or Android
     * (this could also be done through attributes by the developer)
     * @param  SnsDefaultApnMessageContract|SnsDefaultApnMessageContract[] $deviceType
     * @return SnsMessageHandler
     */
    public function targetDeviceType($deviceType): SnsMessageHandler
    {
        $this->deviceTypes = $deviceType;

        return $this;
    }

    /**
     * Sets the attributes we need to use to validate the Filter Policy within SNS
     * @param  array $attributes
     * @return SnsMessageHandler
     */
    public function setMessageAttributes(array $attributes): SnsMessageHandler
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets the message type for the notification
     * @param  string $messageType
     * @return SnsMessageHandler
     */
    public function setMessageType(string $messageType): SnsMessageHandler
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * Adds the content to send with the notification
     * @param  string $title
     * @param  string $message
     * @return SnsMessageHandler
     */
    public function setMessageContent(string $title, string $message): SnsMessageHandler
    {
        $this->title   = $title;
        $this->content = $message;

        return $this;
    }

    /**
     * Sets
     * @param  array $additionalData
     * @return SnsMessageHandler
     */
    public function setAdditionalData(array $additionalData): SnsMessageHandler
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * Sends the notification defined above
     * @param  string $topic_arn
     * @param  string $subject
     * @param  string $structure
     * @return MessageBag|bool
     */
    public function send(string $topic_arn, string $subject, string $structure = 'json'){

        $isValid = $this->validate([
            'title'     => 'required|string|max:100',
            'content'   => 'required|string',
            'subject'   => 'required|string|max:100', //can't be longer than 100 characters
            'topic_arn' => 'required|string' //not sure on what a topic must be (*maybe the same validation as the endpoint?*)
        ], [
            'title'     => $this->title,
            'content'   => $this->content,
            'subject'   => $subject,
            'topic_arn' => $topic_arn
        ]);

        if(!$isValid){
            return $this->getErrors();
        }

        //TODO: How to validate the different platform messages?
        //TODO: Add validation to all parameters given

        //TODO: test is structure can be removed and always be json?
        //TODO: remove json structure from both here and buildMessages if possible
            // -> https://docs.aws.amazon.com/sns/latest/api/API_Publish.html
            // -> See Request Parameters: Message paragraph 1 for different messages per protocol

        //NOTE: If the MessageAttributes aren't sent try MessageStructure as String?
        //https://docs.aws.amazon.com/sns/latest/dg/sns-message-attributes.html
        //https://docs.aws.amazon.com/sns/latest/api/API_MessageAttributeValue.html
        //https://docs.aws.amazon.com/sns/latest/api/API_Publish.html

        //Sends the message to sns with the required data
        Sns::getClient()->publish([
            'TopicArn'          => $topic_arn,
            'Message'           => json_encode($this->buildMessages()),
            'MessageStructure'  => 'json',
//            'MessageStructure' => $structure, THE EXAMPLES IGNORE THIS?,
            'MessageAttributes' => $this->attributes,
            'Subject'           => $subject
        ]);

        //TODO: VALIDATE RESPONSE OF PUBLISH

        //as this class is used once reset messages so it doesn't send the same data twice
        $this->resetInstance();

        return true;
    }

    /**
     * Generates the array of data required for each device type to be sent with the notification to sns
     * @return array
     */
    protected function buildMessages(){
        return collect(Arr::wrap($this->deviceTypes))
            ->mapWithKeys(function($deviceType){

                //we can only create a notification if the device type is valid
                if(is_a($deviceType, SnsDefaultApnMessageContract::class, true)){
                    return [
                        $deviceType::getName() => json_encode($deviceType::getContents($this->title, $this->content, $this->messageType, $this->additionalData))
                    ];
                } else {
                    $this->resetInstance();

                    throw new InvalidMessageTypeException('Message type is not compatible with SnsDefaultApnMessageContract');
                }
            })
            ->toArray();
    }

    protected function resetInstance(){
        $this->deviceTypes = config('sns.default_messages');
        $this->messageType = config('app.name') . ' Notifications';

        $this->attributes     = [];
        $this->additionalData = [];
        $this->title          = '';
        $this->content        = '';
    }
}
