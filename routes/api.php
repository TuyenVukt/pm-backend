<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum', 'verified')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
    Route::get('/test-midd/search/{title}', function ($title) {
        return $title;
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/member_workspace/{id}', [UserController::class,'getAllUserInWorkspace']);
    Route::get('/my_profile', [UserController::class,'getProfile']);
    Route::post('/update_profile', [UserController::class,'updateProfile']);
    Route::post('/update_member/{id}', [UserController::class,'updateMemberProfile']);
    Route::get('/member/{id}', [UserController::class,'getMemberById']);
    //workspace
    Route::post('/workspace', [WorkspaceController::class, 'create']);
    Route::get('/workspace/{id}', [WorkspaceController::class, 'show']);
    Route::get('/get_projects_by_workspace/{id}', [WorkspaceController::class, 'getProjectsByWorkspace']);
    Route::get('/get_members_by_workspace/{id}', [WorkspaceController::class, 'getMembersByWorkspace']);
    Route::post('/workspace/{id}', [WorkspaceController::class, 'edit']);
    Route::delete('/delete_user_workspace/{user_id}', [WorkspaceController::class, 'deleteUser']);
    //project
    Route::post('/project', [ProjectController::class, 'create']);
    Route::post('/add_list_members_to_project', [ProjectController::class, 'addListMembersToProject']);
    Route::get('/project/{id}', [ProjectController::class, 'show']);
    Route::post('/project/{id}', [ProjectController::class, 'update']);
    Route::get('/members_of_project/{id}', [ProjectController::class, 'getAllMembersOfProject']);
    Route::delete('/delete_user_project/{id}', [ProjectController::class, 'removeUserFromProject']);
    Route::get('/members-not-in-project/{project_id}', [ProjectController::class, 'getUsersNotInProject']);
    Route::get('/projects-by-user', [ProjectController::class, 'getProjectsByUserId']);
    Route::get('/project/{project_id}/task_status_count', [ProjectController::class, 'getTaskStatusCount']);
    Route::get('/project/{project_id}/task_milestone_status_count', [ProjectController::class, 'getTaskMilestoneStatusCount']);
    //milestone
    Route::post('/milestone', [MilestoneController::class, 'store']);
    Route::get('/milestone/{id}', [MilestoneController::class, 'show']);
    Route::get('/project/{id}/milestones', [MilestoneController::class, 'getMilestoneByProject']);
    Route::post('/milestone/{id}', [MilestoneController::class, 'update']);
    Route::delete('/milestone/{id}', [MilestoneController::class, 'destroy']);
    //task
    Route::post('/task', [TaskController::class, 'store']);
    Route::post('/task/{id}', [TaskController::class, 'update']);
    Route::get('/task/{id}', [TaskController::class, 'findById']);
    Route::delete('/task/{id}', [TaskController::class, 'destroy']);
    Route::get('/get-tasks/{project_id}', [TaskController::class, 'show']);
    Route::get('/tasks-by-project/{project_id}', [TaskController::class, 'getTasksWithSubTasksInProject']);
    Route::get('/sub_task_by_task/{id}', [TaskController::class, 'getSubTaskByTask']);
    Route::get('/dashboard_tasks', [TaskController::class, 'dashBoardTask']);
    Route::get('/dashboard_update', [WorkspaceController::class, 'getCommentsInDashboard']);
    //Comment
    Route::post('/comment', [CommentController::class, 'createComment']);
    Route::get('/comment/{id}', [ CommentController::class, 'show']);
    Route::delete('/comment/{id}', [ CommentController::class, 'destroy']);
    Route::get('/get-comments-by-task/{id}', [ CommentController::class, 'getCommentsByTask']);
    Route::post('/comment/{id}', [CommentController::class, 'editComment']);
    Route::get('update_in_project/{project_id}', [CommentController::class, 'getCommentsByProject']);
    //document
    Route::post('/document', [DocumentController::class, 'create']);
    Route::delete('/document', [DocumentController::class, 'destroy']);
    Route::post('/document/{id}', [DocumentController::class, 'update']);
    Route::get('/document/{doc_id}', [DocumentController::class, 'get']);
    Route::get('/documents_by_project/{project_id}', [DocumentController::class, 'getFilesByProject']);
    //notification
    Route::get('/notifications/{user_id}', [NotificationController::class, 'getNotiByUser']);
    Route::get('/count_noti', [NotificationController::class, 'countUnreadNotiByUser']);
    Route::post('/read_noti/{id}', [NotificationController::class, 'readNotification']);
    //

});

// Route::post('/upload', [FileController::class, 'upload']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/verify-email/{id}/{token}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::get('/delete', [UserController::class,'deleteImg']);
Route::get('/avatar/{id}', [UserController::class,'showAvatar']);
Route::get('/test', function () {
    return "12345";
});


