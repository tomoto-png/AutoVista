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
        Schema::create('price_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('price_tags')->insert([
            ['name' => '100万以下', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '100万から300万', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '300万から500万', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '500万から700万', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '700万から900万', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '900万以上', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tags');
    }
};
