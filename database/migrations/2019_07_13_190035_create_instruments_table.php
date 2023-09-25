<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstrumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('instrument_token')->unique();
            $table->string('exchange_token')->unique();

            $table->string('tradingsymbol');

            $table->string('name')->nullable();

            $table->float('last_price')->default(0);
            $table->date('expiry')->nullable();

            $table->float('strike')->default(0);
            $table->float('tick_size')->default(0);
            $table->float('lot_size')->default(0);

            $table->string('instrument_type')->nullable();
            $table->string('segment')->nullable();
            $table->string('exchange')->nullable();

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
        Schema::dropIfExists('instruments');
    }
}
