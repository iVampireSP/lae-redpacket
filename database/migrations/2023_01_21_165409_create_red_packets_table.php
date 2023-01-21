<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('red_packets', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->index();
            $table->string('password')->nullable();

            $table->string('greeting')->nullable();

            // 总数量和剩余数量(不是金额)
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('remain')->default(0);

            // 总金额
            $table->decimal('total_amount', 10, 2)->default(0);

            // 剩余金额
            $table->decimal('remaining_amount', 10, 2)->default(0);

            // user_id
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();

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
        Schema::dropIfExists('red_packets');
    }
};
