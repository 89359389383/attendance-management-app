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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // users.id に外部キー制約
            $table->date('work_date'); // 勤務日
            $table->dateTime('clock_in')->nullable();   // 出勤時刻
            $table->dateTime('clock_out')->nullable();  // 退勤時刻
            $table->text('note')->nullable();           // 備考
            $table->enum('status', ['勤務外', '出勤中', '休憩中', '退勤済']);
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
