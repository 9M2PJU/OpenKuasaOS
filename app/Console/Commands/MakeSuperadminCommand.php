<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperadminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'openkuasa:make-superadmin {email : Emel pengguna yang hendak dinaikkan sebagai Superadmin}';

    /**
     * The console command description.
     */
    protected $description = 'Lantik pengguna menjadi Superadmin sistem secara terus melalui CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Pengguna dengan emel [{$email}] tidak dijumpai.");
            return Command::FAILURE;
        }

        $user->is_superadmin = true;
        $user->status = 'active';
        $user->save();

        $this->info("Berjaya! Pengguna [{$user->name}] ({$user->email}) kini telah dinaikkan taraf sebagai Superadmin Penuh.");

        return Command::SUCCESS;
    }
}
