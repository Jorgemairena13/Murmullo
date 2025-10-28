<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Referencia a 'usuarios'
            $table->unsignedBigInteger('post_id'); // Referencia a 'posts'
            $table->timestamps();




            $table->foreign('user_id')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');


            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('cascade');


            $table->unique(['user_id', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
