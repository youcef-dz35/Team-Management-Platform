<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'ceo@example.com')->first();
if ($user) {
    echo "User found: " . $user->name . " (ID: " . $user->id . ")";
    // Verify password is "password" (hash check)
    if (Hash::check('password', $user->password)) {
        echo " | Password matches 'password'";
    } else {
        echo " | Password DOES NOT match 'password'";
    }
} else {
    echo "User NOT found";
}
