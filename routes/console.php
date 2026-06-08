<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:ensure {--email=} {--password=} {--name=Administrator}', function () {
    $email = $this->option('email') ?: env('DEPLOY_ADMIN_EMAIL');
    $password = $this->option('password') ?: env('DEPLOY_ADMIN_PASSWORD');
    $name = $this->option('name') ?: env('DEPLOY_ADMIN_NAME', 'Administrator');

    if (! $email || ! $password) {
        $this->warn('DEPLOY_ADMIN_EMAIL and DEPLOY_ADMIN_PASSWORD are required.');

        return 1;
    }

    User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => $password,
            'role' => 'admin',
            'status' => 'active',
        ],
    );

    $this->info("Administrator account is ready for {$email}.");

    return 0;
})->purpose('Create or update the initial administrator account.');
