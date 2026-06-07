<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('status');
            $table->json('files')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropColumn('progress');
            $table->dropColumn('files');
        });
    }
};
