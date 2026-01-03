<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');

            // 出勤・退勤（修正申請）
            $table->time('corrected_start')->nullable();
            $table->time('corrected_end')->nullable();

            // 休憩（修正申請）
            $table->time('break1_start')->nullable();
            $table->time('break1_end')->nullable();
            $table->time('break2_start')->nullable();
            $table->time('break2_end')->nullable();

            $table->string('remark', 255)->nullable();

            // pending / approved / rejected など
            $table->string('status', 20)->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
}
