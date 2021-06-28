<?php


namespace Imageplus\Sns\InteractionHandlers;


use Illuminate\Contracts\Support\MessageBag;
use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\Traits\instancesSnsModels;
use Imageplus\Sns\Traits\validatesObjects;

/**
 * Class SnsTopicHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsTopicHandler
{
    use instancesSnsModels, validatesObjects;

    /**
     * Gets a topic by returning or creating it
     * @return SnsTopicContract|MessageBag
     */
    public function getTopic($name){
        //attempt to find the topic
        $topic = $this->model::findByName($name)->first();

        //if the topic exists return it otherwise create one
        return $topic ?? $this->createTopic($name);
    }

    /**
     * creates the topic
     * @param  String $name
     * @return SnsTopicContract|bool
     */
    protected function createTopic(string $name){
        //we need to validate the name when creating a new topic
        $isValid = $this->validate([
            'name' => 'required|unique:' . config('sns.tables.topic') . ',name'
        ], [
            'name' => $name
        ]);

        if(!$isValid){
            return false;
        }

        //create the topic in sns with a useful name
        $topic = Sns::getClient()->CreateTopic([
            'Name' => config('sns.topic_prefix') . '--' . $name
        ]);

        //NOTES: SNS throws its own exception
        //TODO: ADD EXCEPTION FOR FAILURE

        //create the topic in the database
        return $this->model::create([
            'name'      => $name,
            'topic_arn' => $topic->get('TopicArn')
        ]);
    }

    /**
     * Deletes an sns topic
     * @param  SnsTopicContract $topic
     * @return bool
     */
    public function removeTopic(SnsTopicContract $topic){
        //removes the topic from sns
        Sns::getClient()->DeleteTopic([
            'TopicArn' => $topic->topic_arn
        ]);

        //NOTES: SNS throws its own exception
        //TODO: ADD EXCEPTION FOR FAILURE

        //remove the topic from the database
        return $topic->delete();
    }
}
