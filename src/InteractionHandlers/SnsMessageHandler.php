<?php


namespace Imageplus\Sns\src\InteractionHandlers;


use Imageplus\Sns\src\Facades\Sns;

/**
 * Class SnsMessageHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsMessageHandler
{
    /**
     * Holds all portions of the message for the different types
     * So they can be added separately then sent at once
     * @var array
     */
    public $messages = [];

    /**
     * Adds a new type of message e.g. APNS and the format of data to send through it
     * @param $type
     * @param $content
     * @return $this
     */
    public function addType($type, $content){
        //add the new message type to the array
        $this->messages[$type] = json_encode($content);

        //return itself so it can be chained
        return $this;
    }

    /**
     * Adds all default messages to the messages array
     * @param string $title
     * @param string $message
     * @param string $type
     * @param mixed $data
     * @return $this
     */
    public function useDefaults(string $title, string $message, string $type, $data){
        //iterate all default message types
        foreach (config('sns.default_messages') as $message_type) {
            //instantiate the class
            $message_type = new $message_type;

            //Append the default content for that type to the messages array with its defined key
            $this->messages[$message_type->getName()] = json_encode(
                $message_type->getContents($title, $message, $type, $data)
            );
        }

        //allow chaining so return this
        return $this;
    }

    /**
     * Sends the message with all the data stored in this instance of the message handler
     * @param $topic_arn
     * @param $subject
     * @param string $structure
     * @return bool
     */
    public function send($topic_arn, $subject, $structure = 'json'){

        //TODO: VALIDATE THE SUBJECT (<= 100 CHARACTERS)

        //Sends the message to sns with the required data
        Sns::getClient()->publish([
            'TopicArn' => $topic_arn,
            'Message' => json_encode($this->messages),
            'MessageStructure' => $structure,
            'Subject' => $subject,
        ]);

        //TODO: VALIDATE RESPONSE OF PUBLISH

        //as this class is used once reset messages so it doesn't send the same data twice
        $this->messages = [];

        return true;
    }
}
