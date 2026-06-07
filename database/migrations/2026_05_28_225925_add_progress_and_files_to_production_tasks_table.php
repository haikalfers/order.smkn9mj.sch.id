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
        Schema::table('production_tasks', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('status');
            $table->json('files')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_tasks', function (Blueprint $table) {
            $table->dropColumn(['progress', 'files']);
        });
    }
};
