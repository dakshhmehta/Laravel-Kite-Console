<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monthly_rsi', function (Blueprint $table) {
            $table->decimal('gain', 10, 2);
            $table->decimal('loss', 10, 2);
        });

        Schema::table('weekly_rsi', function (Blueprint $table) {
            $table->decimal('gain', 10, 2);
            $table->decimal('loss', 10, 2);
        });

        Schema::table('daily_rsi', function (Blueprint $table) {
            $table->decimal('gain', 10, 2);
            $table->decimal('loss', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('monthly_rsi', function (Blueprint $table) {
            $table->dropColumn('gain', 10, 2);
            $table->dropColumn('loss', 10, 2);
        });

        Schema::table('weekly_rsi', function (Blueprint $table) {
            $table->dropColumn('gain', 10, 2);
            $table->dropColumn('loss', 10, 2);
        });

        Schema::table('daily_rsi', function (Blueprint $table) {
            $table->dropColumn('gain', 10, 2);
            $table->dropColumn('loss', 10, 2);
        });
    }
};
