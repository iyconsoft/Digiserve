<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_services', function (Blueprint $table) {
            $table->id();
			$table->string('msisdn');
			$table->string('name');
			$table->string('service');
			$table->string('service_option');
			$table->string('meter_no');
			$table->string('account_no')->nullable();
			$table->string('bank')->nullable();
			$table->string('payment_reference')->nullable();
			$table->float('amount');
			$table->char('notification_type',1);
			$table->datetime('last_notification');
			$table->datetime('next_notification');
			$table->string('notificaton_message');
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
        Schema::dropIfExists('user_services');
    }
}
