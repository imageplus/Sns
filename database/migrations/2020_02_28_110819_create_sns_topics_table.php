<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnsTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('sns.tables.topic'), function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('model_type');
            $table->integer('model_id')->unsigned();

            $table->string('topic_arn');

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
        Schema::dropIfExists(config('sns.tables.topic'));
    }
}
