<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->string('provider');
			$table->char('notification_type',1);
			$table->string('format');
			$table->char('status',1);
			$table->unsignedBigInteger('option_id')->nullable();
			$table->foreign('option_id')->references('id')->on('options');
			$table->float('fee')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
