<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255); // 名前、必須
            $table->string('email', 255)->unique(); // メールアドレス、必須
            $table->timestamp('email_verified_at')->nullable(); // メール認証用（nullable）
            $table->string('password', 255); // パスワード、必須
            $table->boolean('is_admin')->default(false); // 管理者フラグ（初期値false）
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
