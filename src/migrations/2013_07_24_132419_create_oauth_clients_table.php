<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauthClientsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('id', 40);
            $table->string('secret', 40);
            $table->string('name');
            $table->integer('request_limit')->default(500);
            $table->integer('current_total_request');
            $table->timestamp('request_limit_until');
            $table->timestamp('last_request_at');
            $table->timestamps();

            $table->unique('id');
            $table->unique(array('id', 'secret'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_clients');
    }
}
