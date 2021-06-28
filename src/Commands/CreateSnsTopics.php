<?php

namespace Imageplus\Sns\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Imageplus\Sns\Contracts\SnsTopicContract;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\SnsManager;

class CreateSnsTopics extends Command
{
    protected $signature   = 'sns:create-topics {--once : Only creates 1 topic}';

    protected $description = 'Creates and registers topics with Amazon SNS';

    public function handle(){
        $createMore = true;

        while($createMore){
            $this->handleTopic();

            $createMore = $this->option('once')
                ? false
                : $this->confirm('Add Another Topic?');
        }
    }

    protected function handleTopic(){
        while(true) {
            $name = $this->ask('What is the name of this topic?');

            $topic = Sns::findOrCreateTopic($name);

            if(is_a($topic, SnsTopicContract::class)){
                $this->info("Topic {$topic->topic_arn} Added Successfully");

                return $topic;
            } else {
                collect(Sns::getErrors())
                    ->each(function ($errors) {
                        collect(Arr::wrap($errors))
                            ->each(function ($error) {
                                $this->error($error);
                            });
                    });
            }
        }
    }
}
