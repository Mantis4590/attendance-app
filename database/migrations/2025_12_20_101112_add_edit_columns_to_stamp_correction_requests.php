<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEditColumnsToStampCorrectionRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            // 修正後の出退勤
            $table->time('clock_in')->nullable()->after('attendance_id');
            $table->time('clock_out')->nullable()->after('clock_in');

            // 修正後の休憩
            $table->json('breaks')->nullable()->after('clock_out');

            // 修正後の備考
            $table->text('note')->nullable()->after('breaks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            //
        });
    }
}
