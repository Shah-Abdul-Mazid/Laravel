<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')  // <- FK column name: 'course_id' (references courses.id)
                ->constrained()  // <- Defaults to 'courses.id'
                ->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->index('course_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
};