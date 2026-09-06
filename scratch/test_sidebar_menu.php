<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

$view = view('components.admin.sidebar')->render();

echo "Sidebar view rendered successfully. Contains 'Dashboard': " . (str_contains($view, '<span>Dashboard</span>') ? 'Yes' : 'No') . "\n";
echo "Contains 'Student Achievements': " . (str_contains($view, '<span>Student Achievements</span>') ? 'Yes' : 'No') . "\n";
