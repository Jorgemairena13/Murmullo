<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->unique(['seguidor_id', 'seguido_id'], 'unique_follow');
        });
    }

    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropUnique('unique_follow');
        });
    }
};
