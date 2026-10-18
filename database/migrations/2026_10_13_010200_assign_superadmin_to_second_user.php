<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Akaun Pertama (Demo): admin@openkuasa.my
        $firstUser = User::where('email', 'admin@openkuasa.my')->first()
            ?? User::orderBy('created_at', 'asc')->first();

        if ($firstUser) {
            $firstUser->update([
                'is_demo' => true,
                'is_superadmin' => false,
            ]);
        }

        // 2. Akaun Kedua (Superadmin): admin@sabily.info
        $secondUser = User::where('email', 'admin@sabily.info')->first()
            ?? User::orderBy('created_at', 'asc')->skip(1)->first();

        if ($secondUser) {
            $secondUser->update([
                'is_superadmin' => true,
                'is_demo' => false,
                'status' => 'active',
            ]);

            DB::table('system_settings')
                ->where('key', 'owner_user_id')
                ->update(['value' => json_encode($secondUser->id)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::query()->update([
            'is_superadmin' => false,
            'is_demo' => false,
        ]);
    }
};
