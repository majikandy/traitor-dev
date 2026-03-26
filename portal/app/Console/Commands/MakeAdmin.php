<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'user:admin {email}';
    protected $description = 'Grant admin access to a user';

    public function handle(): void
    {
        $user = User::where('email', $this->argument('email'))->firstOrFail();
        $user->update(['is_admin' => true]);
        $this->info("Admin granted: {$user->email}");
    }
}
