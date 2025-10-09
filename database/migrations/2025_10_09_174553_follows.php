<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('follows',function(Blueprint $table) {
            $table->unsignedBigInteger('seguidor_id');
            $table->unsignedBigInteger('seguido_id');

            $table->timestamps();

            // Claves foraneas
            $table->foreign('seguidor_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('seguido_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
