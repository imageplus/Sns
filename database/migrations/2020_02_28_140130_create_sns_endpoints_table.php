<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnsEndpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('sns.tables.endpoint'), function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('platform')->unique();
            $table->string('device_token');
            $table->text('user_agent')->nullable();

            $table->string('endpoint_arn')->nullable();

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
        Schema::dropIfExists(config('sns.tables.endpoint'));
    }
}
