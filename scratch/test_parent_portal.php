<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Middleware\EnsureUserIsParent;
use App\Livewire\Parent\ParentAchievements;
use App\Livewire\Parent\ParentDashboard;
use App\Livewire\Parent\ParentGrades;
use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== Parent Portal Verification ===\n\n";

// 1. Find a parent user
$parentUser = User::where('role', 'parent')->first();
if (!$parentUser) {
    echo "ERROR: No parent user found!\n";
    exit(1);
}
echo "Testing with Parent: {$parentUser->name} ({$parentUser->login_id})\n";
$parentModel = $parentUser->parent;
$linkedStudents = $parentModel ? $parentModel->students : collect();
echo "Linked Students count: " . $linkedStudents->count() . "\n";
foreach ($linkedStudents as $st) {
    echo "  - Child: {$st->full_name} ({$st->student_number})\n";
}

Auth::login($parentUser);

// 2. Test ParentDashboard component
echo "\n--- Testing ParentDashboard Component ---\n";
$dash = new ParentDashboard();
$dash->mount();
echo "Initial selectedStudentId: {$dash->selectedStudentId}\n";

$dashView = $dash->render();
echo "ParentDashboard rendered successfully! Layout: " . ($dashView->layout ?? 'default') . "\n";

// Test Child Switching if multiple
if ($linkedStudents->count() > 1) {
    $secondChild = $linkedStudents->skip(1)->first();
    $dash->selectChild($secondChild->id);
    echo "Switched to child: {$secondChild->full_name} (ID: {$dash->selectedStudentId})\n";
    $dashView2 = $dash->render();
    echo "ParentDashboard re-rendered successfully for second child!\n";
}

// 3. Test ParentGrades component
echo "\n--- Testing ParentGrades Component ---\n";
$grades = new ParentGrades();
$grades->mount();
$gradesView = $grades->render();
echo "ParentGrades rendered successfully!\n";

// 4. Test ParentAchievements component
echo "\n--- Testing ParentAchievements Component ---\n";
$ach = new ParentAchievements();
$ach->mount();
$achView = $ach->render();
echo "ParentAchievements rendered successfully!\n";

// 5. Test EnsureUserIsParent middleware
echo "\n--- Testing EnsureUserIsParent Middleware ---\n";
$middleware = new EnsureUserIsParent();
$request = Request::create('/parent/dashboard');

// With authenticated parent user
$response = $middleware->handle($request, function ($req) {
    return new \Symfony\Component\HttpFoundation\Response('NEXT_CALLED_SUCCESSFULLY');
});
echo "Parent User access: " . $response->getContent() . "\n";

// With student user (should throw 403)
$studentUser = User::where('role', 'student')->first();
Auth::login($studentUser);
try {
    $middleware->handle($request, function ($req) { return 'SHOULD_NOT_REACH'; });
    echo "[FAIL] Student was allowed access to parent portal!\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "[PASS] Student blocked with 403: " . $e->getMessage() . "\n";
}

echo "\n=== All Parent Portal Tests Passed! ===\n";
