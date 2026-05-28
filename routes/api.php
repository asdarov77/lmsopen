<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AircraftController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Group2learningController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PrivateController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GradeBoundaryController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AukstructureController;
use App\Http\Controllers\SearchController2;
use App\Http\Controllers\UsersCoursesController;
use App\Http\Controllers\ClearDBController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PrivateManiController;
use App\Http\Controllers\StudentAnswersController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'getUserList']);
    Route::get('/users/{id}', [AuthController::class, 'getUser']);
    Route::put('/users/{id}', [AuthController::class, 'update'])->middleware('permission:manage-users');
    Route::put('/users/{id}/password', [AuthController::class, 'chpass'])->middleware('permission:manage-users');
    Route::delete('/users/{id}', [AuthController::class, 'destroy'])->middleware('permission:manage-users');
    Route::post('/users/{id}/roles', [AuthController::class, 'syncRoles'])->middleware('permission:manage-users');
    Route::post('/group2learning', [AuthController::class, 'group2learning']);

    // User courses (self)
    Route::get('/user/courses', function (Request $request) {
        $user = $request->user();
        $groupIds = \App\Models\Group::where('id', $user->group_id)->pluck('id');
        $courseIds = \App\Models\Group2learning::whereIn('group_id', $groupIds)->pluck('course_id');
        $courses = \App\Models\Course::whereIn('id', $courseIds)
            ->with(['categories', 'aircraft', 'aukstructures'])
            ->get();
        return response()->json($courses);
    });

    // User groups
    Route::get('/users/{id}/groups', function ($id) {
        $user = \App\Models\User::with('group')->findOrFail($id);
        return response()->json($user->group ? [$user->group] : []);
    });

    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/course', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/courses/{id}/link', [CourseController::class, 'getLink']);
    Route::get('/getlink/{id}', [CourseController::class, 'getLink']);
    Route::post('/courses', [CourseController::class, 'store'])->middleware('permission:manage-users');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->middleware('permission:manage-users');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:manage-users');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('permission:manage-users');

    // Aircraft
    Route::get('/aircrafts', [AircraftController::class, 'index']);
    Route::get('/aircrafts/scan', [AircraftController::class, 'scanStorage'])->middleware('permission:manage-users');
    Route::post('/aircrafts/scan/{id}/load', [AircraftController::class, 'loadCourses'])->middleware('permission:manage-users');
    Route::get('/aircrafts/showclassesfs', [AircraftController::class, 'showClassesFs'])->middleware('permission:manage-users');
    Route::post('/aircrafts', [AircraftController::class, 'store'])->middleware('permission:manage-users');
    Route::post('/aircrafts/clear', [AircraftController::class, 'clearDatabase'])->middleware('permission:manage-users');

    // Questions
    Route::get('/questions', [QuestionsController::class, 'index']);
    Route::get('/questions/{id}', [QuestionsController::class, 'show']);
    Route::post('/questions', [QuestionsController::class, 'store'])->middleware('permission:manage-users');
    Route::put('/questions/{id}', [QuestionsController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/questions/{id}', [QuestionsController::class, 'destroy'])->middleware('permission:manage-users');

    // Groups
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/{id}', [GroupController::class, 'show']);
    Route::post('/groups', [GroupController::class, 'store'])->middleware('permission:manage-users');
    Route::put('/groups/{id}', [GroupController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->middleware('permission:manage-users');

    // Group2learnings
    Route::get('/group2learnings', [Group2learningController::class, 'index']);
    Route::get('/group2learnings/{id}', [Group2learningController::class, 'show']);
    Route::put('/group2learnings/{id}', [Group2learningController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/group2learnings/{id}', [Group2learningController::class, 'destroy'])->middleware('permission:manage-users');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:manage-users');
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:manage-users');
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->middleware('permission:manage-users');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:manage-users');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:manage-users');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:manage-users');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:manage-users');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:manage-users');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:manage-users');
    Route::post('/roles/{id}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:manage-users');

    // Private files streaming
    Route::get('/private/{aircraft}/{auk}/{path?}', [PrivateController::class, 'stream'])
        ->where('path', '.*');

    // Search
    Route::post('/search', [SearchController::class, 'search']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{course_id}', [FavoriteController::class, 'remove']);

    // Grade boundaries
    Route::get('/grade-boundaries', [GradeBoundaryController::class, 'index']);
    Route::post('/grade-boundaries', [GradeBoundaryController::class, 'store']);

    // Gift import
    Route::apiResource('gift', GiftController::class)->only(['index', 'store'])->middleware('permission:manage-users');
    Route::post('/gift/import', [GiftController::class, 'store'])->middleware('permission:manage-users');
    Route::delete('/gift-clear', [GiftController::class, 'truncate'])->middleware('permission:manage-users');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::put('/calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('/calendar/{id}', [CalendarController::class, 'destroy']);

    // Files
    Route::get('/files', [FilesController::class, 'indexAll']);
    Route::post('/files/add', [FilesController::class, 'upload']);
    Route::get('/files/{type}/{id?}', [FilesController::class, 'index']);
    Route::post('/files', [FilesController::class, 'store']);
    Route::put('/files/{id}', [FilesController::class, 'edit']);
    Route::delete('/files/{id}', [FilesController::class, 'destroy']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('permission:manage-users');
    Route::post('/settings', [SettingsController::class, 'update'])->middleware('permission:manage-users');

    // Student answers
    Route::post('/student-answers', [StudentAnswersController::class, 'store']);

    // Aukstructures
    Route::apiResource('aukstructure', AukstructureController::class);
    Route::get('/getfirstauk/{id}', [CourseController::class, 'getFirstAuk']);
    Route::get('/coursemanifest/{id}', [CourseController::class, 'showManifest']);
    Route::get('/lessons', [AukstructureController::class, 'lessons']);

    // Private manifest
    Route::get('/private/{aircraft}/{auk}/imsmanifest.xml', [PrivateManiController::class, 'xmles00']);

    // User courses
    Route::get('/userauks', [UsersCoursesController::class, 'index']);

    // Search
    Route::post('/search-files2', [SearchController2::class, 'search']);

    // Clear database
    Route::post('/clear-database', [ClearDBController::class, 'clear'])->middleware('permission:manage-users');
    Route::post('/aircrafts/clear', [AircraftController::class, 'clearDatabase'])->middleware('permission:manage-users');
});
