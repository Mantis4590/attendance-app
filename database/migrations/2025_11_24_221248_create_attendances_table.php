<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // 誰の勤怠か
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 勤怠日
            $table->date('date');

            // 出勤・退勤
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();

            // 合計休憩時間
            $table->time('total_break')->nullable();

            // 勤務時間（合計）
            $table->time('total_work')->nullable();

            // 備考
            $table->text('note')->nullable();

            // ステータス（勤務中・退勤済み・勤務外など）
            $table->string('status')->default('working');

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
        Schema::dropIfExists('attendances');
    }
}
