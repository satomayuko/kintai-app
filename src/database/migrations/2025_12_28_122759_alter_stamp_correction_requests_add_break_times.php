<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            // 休憩の開始/終了（1回目）
            $table->time('break1_start')->nullable()->after('corrected_end');
            $table->time('break1_end')->nullable()->after('break1_start');

            // 休憩の開始/終了（2回目）
            $table->time('break2_start')->nullable()->after('break1_end');
            $table->time('break2_end')->nullable()->after('break2_start');

            // もし corrected_break をもう使わないなら削除（残したいならこの行は消してOK）
            if (Schema::hasColumn('stamp_correction_requests', 'corrected_break')) {
                $table->dropColumn('corrected_break');
            }

            // status にデフォルト付けたい場合（既にあるなら不要）
            // $table->string('status', 20)->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            if (Schema::hasColumn('stamp_correction_requests', 'break1_start')) $table->dropColumn('break1_start');
            if (Schema::hasColumn('stamp_correction_requests', 'break1_end')) $table->dropColumn('break1_end');
            if (Schema::hasColumn('stamp_correction_requests', 'break2_start')) $table->dropColumn('break2_start');
            if (Schema::hasColumn('stamp_correction_requests', 'break2_end')) $table->dropColumn('break2_end');

            // downで corrected_break を戻す（必要なら）
            // $table->integer('corrected_break')->nullable();
        });
    }
};
