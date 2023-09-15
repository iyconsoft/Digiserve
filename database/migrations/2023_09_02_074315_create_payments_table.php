<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('user_service_id')->nullable();
			$table->foreign('user_service_id')->references('id')->on('user_services');
			$table->string('payment_reference');
			$table->float('amount');
			$table->char('is_paid',1);
			$table->datetime('service_delivered');
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
        Schema::dropIfExists('payments');
    }
}
