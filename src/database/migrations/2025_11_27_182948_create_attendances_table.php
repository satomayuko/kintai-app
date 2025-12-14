<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 20)->default('勤務外');
            $table->string('remark', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
