<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 100)->unique();
            $table->jsonb('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('system_settings')->insert([
            [
                'id' => (string) Str::uuid(),
                'key' => 'registration_enabled',
                'value' => json_encode(true),
                'description' => 'Membolehkan atau menutup pendaftaran awam pada login/register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'key' => 'instance_name',
                'value' => json_encode('OpenKuasaOS Self-Hosted'),
                'description' => 'Nama instalasi pelayan OpenKuasaOS',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'key' => 'owner_user_id',
                'value' => json_encode('01a130a3-db17-73c4-8584-ea2d1dd4ea4e'),
                'description' => 'ID pengguna pemilik utama sistem self-hosted',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
