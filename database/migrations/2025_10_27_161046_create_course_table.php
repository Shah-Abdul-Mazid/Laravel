<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();  // <- Standard PK: auto-increment 'id' (bigint unsigned)
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('feature_video')->nullable(); // Path to uploaded video
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};