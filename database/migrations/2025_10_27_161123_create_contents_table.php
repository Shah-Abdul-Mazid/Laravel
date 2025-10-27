<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'video', 'link']);
            $table->text('data'); // JSON-like: for text/link it's the value, for image/video it's path
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->index('module_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contents');
    }
};