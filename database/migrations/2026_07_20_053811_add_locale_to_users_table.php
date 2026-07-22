<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->nullable()->after('role');
            $table->string('locale', 5)->default('en')->after('mobile');
            $table->string('profile_image')->nullable()->after('locale');
            $table->tinyInteger('is_active')->default(1)->after('profile_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile');
            $table->dropColumn('locale');
            $table->dropColumn('profile_image');
            $table->dropColumn('is_active');
        });
    }
};
