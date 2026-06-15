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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->json('layout_config');
            $table->json('images')->nullable();
            $table->timestamps();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

            DB::statement('CREATE INDEX themes_title_trgm_idx ON themes USING GIN (title gin_trgm_ops);');

            DB::statement('CREATE INDEX themes_description_trgm_idx ON themes USING GIN (description gin_trgm_ops);');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS themes_title_trgm_idx');
            DB::statement('DROP INDEX IF EXISTS themes_description_trgm_idx');
        }

        Schema::dropIfExists('themes');
    }
};
