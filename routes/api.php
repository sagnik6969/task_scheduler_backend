<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// user - authentication 
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn () => auth()->user());

    Route::prefix('user')->group(function () {
        Route::get('tasks', [UserTaskController::class, 'index']);
        Route::post('tasks', [UserTaskController::class, 'store']);
        Route::get('tasks/{task}', [UserTaskController::class, 'show']);
        Route::put('tasks/{task}', [UserTaskController::class, 'update']);
        Route::delete('tasks/{task}', [UserTaskController::class, 'destroy']);
        //for efficiency
        Route::get('efficiency', [UserTaskController::class, 'calculateOverallEfficiency']);


        // pie chart data routes 
        Route::get('analysis', [UserTaskController::class, 'userTasksAnalysis']); // checked '
        Route::get('notifications', [UserTaskController::class, 'getNotifications']);
        Route::post('notifications/mark_as_read', [UserTaskController::class, 'makeNotificationsAsRead']);
    });

    Route::prefix('admin')->group(function () {
        Route::get('tasks', [AdminTaskController::class, 'index']); //checked
        Route::delete('tasks/{task}', [AdminTaskController::class, 'destroy']); //checked 
        Route::post('assign-task/{user}', [AdminTaskController::class, 'assignTaskToUser']); //code written just wait for frontend 
        Route::get('users/{user}', [AdminTaskController::class, 'userTasks']); // checked
        Route::patch('users/{user}', [AdminTaskController::class, 'makeAdmin']); // checked 
        Route::get('users', [UserController::class, 'index']); // no need as 1st route is giving same functionality
        Route::delete('users/{user}', [UserController::class, 'destroy']); // checked

        // filters routes are left 

        // pie chart data routes
        Route::get('analysis', [AdminTaskController::class, 'allUSerAnalysys']); // checked
        Route::get('analysis/all_user_task_progress_analysis', [AdminTaskController::class, 'allUserTaskProgressAnalysis']); //checked
        // Route::get('analysis/{user}', [AdminTaskController::class, 'userTaskAnalysis']); //checked

        // assigned task list 
        Route::get('/assign/tasks', [TaskAssignmentController::class, 'allAssignTasks']);
    });
});

Route::get('/tasks/assign/{taskId}/{token}', [TaskAssignmentController::class, 'assignTask'])->where('token', '.*'); // part of assignTaskToUser
Route::patch('/tasks/assign/{taskId}', [TaskAssignmentController::class, 'assignTaskUpdates']);


Route::post('/register', [AuthController::class, 'register']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'handleEmailVerificationRedirect'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [UserController::class, 'sendEmailVerificationNotification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout']);
