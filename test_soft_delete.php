<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::factory()->create();
echo "Before delete: " . ($user->fresh() ? 'Exists' : 'Null') . "\n";
$user->delete();
echo "After delete: " . ($user->fresh() ? 'Exists' : 'Null') . "\n";
