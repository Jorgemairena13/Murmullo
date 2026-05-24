<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE f1 FROM follows f1
            INNER JOIN follows f2
            WHERE f1.seguidor_id = f2.seguidor_id
              AND f1.seguido_id = f2.seguido_id
              AND f1.created_at > f2.created_at
        ');

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
