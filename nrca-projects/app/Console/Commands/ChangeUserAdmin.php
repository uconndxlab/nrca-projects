<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ChangeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nrca:admin {netid} {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user if they do not exist, and change their admin status.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $netid = $this->argument('netid');
        $remove = $this->option('remove');

        $user = User::where('netid', $netid)->first();
        if (!$user) {
            $user = new User();
            $user->name = $netid;
            $user->email = $netid . '@uconn.edu';
            $user->netid = $netid;
            $user->save();
            $this->info("User $netid created.");
        }

        if ($remove) {
            $user->is_admin = false;
            $this->info("User $netid is no longer an admin.");
        } else {
            $user->is_admin = true;
            $this->info("User $netid is now an admin.");
        }
        $user->save();
    }
}
