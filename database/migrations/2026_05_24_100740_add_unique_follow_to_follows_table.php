<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('follows');

        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seguidor_id');
            $table->unsignedBigInteger('seguido_id');
            $table->timestamps();

            $table->foreign('seguidor_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('seguido_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->unique(['seguidor_id', 'seguido_id'], 'unique_follow');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
