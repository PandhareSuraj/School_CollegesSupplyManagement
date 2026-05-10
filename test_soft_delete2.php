<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::factory()->create();
echo "Before delete deleted_at: " . $user->deleted_at . "\n";
$user->delete();
echo "After delete deleted_at: " . $user->deleted_at . "\n";

$fresh = User::find($user->id);
echo "User::find(): " . ($fresh ? 'Exists' : 'Null') . "\n";
$fresh2 = $user->fresh();
echo "fresh(): " . ($fresh2 ? 'Exists' : 'Null') . "\n";
