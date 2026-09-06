<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

echo "Logged in as: " . auth()->user()->login_id . "\n";

$req = \Illuminate\Http\Request::create('/logout', 'POST');
$req->setLaravelSession(app('session')->driver());

$response = app()->handle($req);

echo "Logout response status: " . $response->getStatusCode() . " (Redirect URL: " . $response->headers->get('Location') . ")\n";
echo "Authenticated after logout? " . (auth()->check() ? 'Yes' : 'No') . "\n";
