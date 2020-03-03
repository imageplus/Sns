<?php


namespace Imageplus\Sns\src\InteractionHandlers;


use Imageplus\Sns\src\Contracts\SnsTopicContract;
use Imageplus\Sns\src\Facades\Sns;
use Imageplus\Sns\src\Traits\instancesSnsModels;
use Illuminate\Database\Eloquent\Model;

/**
 * 1 Topic per base model
 * Class SnsTopicHandler
 * @package Imageplus\Sns\InteractionHandlers
 * @author Harry Hindson
 */
class SnsTopicHandler
{
    use instancesSnsModels;

    /**
     * Gets a topic by returning or creating it
     * @param Model $model
     * @return SnsTopicContract|void
     */
    public function getTopic(Model $model){
        //A topic can only be added if the sns_topic method exists
        if(method_exists($model, 'sns_topic')){
            //if the model has a topic return it
            if($model->sns_topic){
                return $model->sns_topic;
            }

            //otherwise create the topic
            return $this->createTopic($model);
        }

        //TODO: ADD EXCEPTION FOR INVALID MODEL
    }

    /**
     * creates the topic
     * @param Model $model
     * @return SnsTopicContract|void
     */
    protected function createTopic(Model $model){
        //create the topic in sns with a useful name
        $topic = Sns::getClient()->CreateTopic([
            'Name' => config('sns.topic_prefix') . '--' . class_basename($model) . '-' . $model->id
        ]);

        //TODO: ADD EXCEPTION FOR FAILURE

        //create the topic in the database
        return $model->sns_topic()->create([
            'topic_arn' => $topic->get('TopicArn')
        ]);
    }

    /**
     * Deletes an sns topic
     * @param SnsTopicContract $topic
     * @return bool
     */
    public function removeTopic(SnsTopicContract $topic){
        //removes the topic from sns
        Sns::getClient()->DeleteTopic([
            'TopicArn' => $topic->topic_arn
        ]);

        //TODO: ADD EXCEPTION FOR FAILURE

        //remove the topic from the database
        return $topic->delete();
    }
}
