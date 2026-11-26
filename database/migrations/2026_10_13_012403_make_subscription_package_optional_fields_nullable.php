<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('color')->nullable()->change();
            $table->integer('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasNullFields = DB::table('subscription_packages')
            ->whereNull('description')
            ->orWhereNull('color')
            ->orWhereNull('price')
            ->exists();

        if ($hasNullFields) {
            throw new RuntimeException('Fill all optional subscription package fields before rolling back this migration.');
        }

        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('color')->nullable(false)->change();
            $table->integer('price')->nullable(false)->change();
        });
    }
};
