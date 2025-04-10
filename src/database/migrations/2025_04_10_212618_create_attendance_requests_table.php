<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // users.id に外部キー制約
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade'); // attendances.id に外部キー制約
            $table->date('request_date');   // 申請日
            $table->dateTime('clock_in')->nullable();        // 修正：出勤時刻
            $table->dateTime('clock_out')->nullable();       // 修正：退勤時刻
            $table->text('note');           // 備考（NOT NULL）
            $table->enum('status', ['承認待ち', '承認済み']); // ステータス
            $table->unsignedBigInteger('approved_by')->nullable(); // 承認者（users.id）
            $table->dateTime('approved_at')->nullable();     // 承認日時
            $table->timestamps();

            // 外部キー制約（approved_by も users.id）
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
