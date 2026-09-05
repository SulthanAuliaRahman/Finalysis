<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_configuration', function (Blueprint $table) {
            $table->string('name')->default('Default')->after('id');
            $table->boolean('is_active')->default(false)->after('llm_api_key');
            $table->unsignedInteger('priority')->default(0)->after('is_active');
            $table->boolean('is_limited')->default(false)->after('priority');
            $table->timestamp('limited_until')->nullable()->after('is_limited');
            $table->unsignedInteger('request_count')->default(0)->after('limited_until');
            $table->timestamp('last_request_at')->nullable()->after('request_count');
        });

        // Set the first existing config as active
        $first = DB::table('ai_configuration')->orderBy('id')->first();
        if ($first) {
            DB::table('ai_configuration')
                ->where('id', $first->id)
                ->update(['is_active' => true, 'priority' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_configuration', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'is_active',
                'priority',
                'is_limited',
                'limited_until',
                'request_count',
                'last_request_at',
            ]);
        });
    }
};
