<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
  public function up() {
    Schema::create('users', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      
      $table->id();
      $table->string('first_name', 100);
      $table->string('last_name', 100);
      $table->string('user_type', 40)->default('player');
      $table->boolean('can_play_goalie')->default(0);
      $table->smallInteger('ranking');
      $table->timestamps();
    });
  }

  public function down() {
    Schema::dropIfExists('users');
  }
}
