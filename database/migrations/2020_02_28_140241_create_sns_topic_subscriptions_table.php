<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnsTopicSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('sns.tables.subscription'), function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('sns_topic_id')->unsigned();
            $table->foreign('sns_topic_id')->references('id')->on('sns_topics');

            $table->bigInteger('sns_endpoint_id')->unsigned()->unique();
            $table->foreign('sns_endpoint_id')->references('id')->on('sns_endpoints');

            $table->string('subscription_arn');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('sns.tables.subscription'));
    }
}
