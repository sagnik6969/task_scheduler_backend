<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTaskController;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn() => auth()->user());

    Route::prefix('user')->group(function () {
        Route::get('tasks', [UserTaskController::class, 'index']); //required
        Route::post('tasks', [UserTaskController::class, 'store']); //required
        Route::get('tasks/{task}', [UserTaskController::class, 'show']); // not used in frontend
        Route::put('tasks/{task}', [UserTaskController::class, 'update']); //required
        Route::delete('tasks/{task}', [UserTaskController::class, 'destroy']); //required
        //for efficiency
        Route::get('efficiency', [UserTaskController::class, 'calculateOverallEfficiency']); //required


        // pie chart data routes 
        Route::get('analysis', [UserTaskController::class, 'userTasksAnalysis']); // required 

        //notification
        Route::get('notifications', [UserTaskController::class, 'getNotifications']); //required
        Route::post('notifications/mark_as_read', [UserTaskController::class, 'makeNotificationsAsRead']); //required
    });

    Route::prefix('admin')->group(function () {
        Route::get('tasks', [AdminTaskController::class, 'index']); //checked -> required
        Route::delete('tasks/{task}', [AdminTaskController::class, 'destroy']); //checked  -> required
        Route::post('assign-task/{user}', [AdminTaskController::class, 'assignTaskToUser']); // ->required
        Route::get('users/{user}', [AdminTaskController::class, 'singleUserWithTasks']); // checked //required
        Route::patch('users/{user}', [AdminTaskController::class, 'makeAdmin']); // checked //required
        // Route::get('users', [UserController::class, 'index']); // no need as 1st route is giving same functionality
        Route::delete('users/{user}', [UserController::class, 'destroy']); // checked ->required

        // filters routes are left 

        // pie chart data routes
        // Route::get('analysis', [AdminTaskController::class, 'allUSerAnalysys']); // checked
        Route::get('analysis/all_user_task_progress_analysis', [AdminTaskController::class, 'allUserTaskProgressAnalysis']);
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
// Route::post('/email/verification-notification', [UserController::class, 'sendEmailVerificationNotification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
