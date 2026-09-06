<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$validatorSuccess = \Illuminate\Support\Facades\Validator::make(
    ['contact_number' => '9123456789'],
    ['contact_number' => ['nullable', 'regex:/^[0-9]{10}$/']]
);

$validatorFailLetter = \Illuminate\Support\Facades\Validator::make(
    ['contact_number' => '912345678a'],
    ['contact_number' => ['nullable', 'regex:/^[0-9]{10}$/']]
);

$validatorFailLength = \Illuminate\Support\Facades\Validator::make(
    ['contact_number' => '09123456789'],
    ['contact_number' => ['nullable', 'regex:/^[0-9]{10}$/']]
);

echo "10 Digits Test: " . ($validatorSuccess->passes() ? "PASSED" : "FAILED") . "\n";
echo "Letter Test: " . ($validatorFailLetter->fails() ? "PASSED (Rejected)" : "FAILED") . "\n";
echo "Length 11 Test: " . ($validatorFailLength->fails() ? "PASSED (Rejected)" : "FAILED") . "\n";
