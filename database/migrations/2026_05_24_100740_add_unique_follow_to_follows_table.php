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
            CREATE TABLE follows_temp AS
            SELECT MIN(created_at) as created_at, MIN(updated_at) as updated_at,
                   seguidor_id, seguido_id
            FROM follows
            GROUP BY seguidor_id, seguido_id
        ');

        DB::statement('TRUNCATE TABLE follows');

        DB::statement('
            INSERT INTO follows (seguidor_id, seguido_id, created_at, updated_at)
            SELECT seguidor_id, seguido_id, created_at, updated_at FROM follows_temp
        ');

        DB::statement('DROP TABLE follows_temp');

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
