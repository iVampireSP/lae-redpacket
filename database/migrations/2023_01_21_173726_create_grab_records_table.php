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
    public function up(): void
    {
        Schema::create('grab_records', function (Blueprint $table) {
            $table->id();

            // red packet id
            $table->unsignedBigInteger('red_packet_id')->index();
            $table->foreign('red_packet_id')->references('id')->on('red_packets');

            // 金额
            $table->decimal('amount', 10, 2)->default(0);

            // user id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('grab_records');
    }
};
