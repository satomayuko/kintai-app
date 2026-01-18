cat > src/database/migrations/2026_01_18_123150_create_stamp_correction_request_breaks_table.php <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stamp_correction_request_id');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['stamp_correction_request_id', 'sort_order'], 'scrb_req_sort_idx');

            $table->foreign('stamp_correction_request_id', 'scrb_req_fk')
                ->references('id')
                ->on('stamp_correction_requests')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_request_breaks');
    }
};