<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Http\Request;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
        /**
     * The response factory implementation.
     *
     * @var ResponseFactory
     */
    protected $response;

    /**
     * Create a new controller instance.
     *
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    /**
     * Return a JSON response.
     *
     * @param string $status
     * @param string $message
     * @param int $code_bug
     * @param array $data
     * @param int $status_code
     * @return JsonResponse
     */
    protected function jsonResponse($status, $message, $data = [], $status_code = 200): JsonResponse
    {
        return $this->response->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }

    protected function makeNotification($user_id, $key, $type): bool
    {
        if($type === 1){
            $content = "Task: ".$key.". You have been assigned a task";
            $noti = Notification::create([
                'content'           => $content,
                'user_id'           => $user_id,
            ]);
            if($noti) return true;
        } else if($type === 2){
            $content = "Task: ".$key.". The task you were assigned has been edited";
            $noti = Notification::create([
                'content'           => $content,
                'user_id'           => $user_id,
            ]);

            if($noti) return true;
        }  else if($type === 3){
            $content = "Task: ".$key.". The task you were assigned had a comment";
            $noti = Notification::create([
                'content'           => $content,
                'user_id'           => $user_id,
            ]);
            
            if($noti) return true;
        }

        return false;

    }

    

    protected function checkInsideProject(Request $request, $project_id): bool {
        $projects = $request->user()->projects;
        $foundProject = collect($projects)->firstWhere('id', $project_id);
        if($foundProject) return true;
        return false;
    }
    
    protected function autoMakeComment(Resquest $request, $type){
        //type == 1 => tạo task
        //type == 2 => edit task
        //type == 3 =>
        if($type === 1){
            $content = "added a new Task";
            $comment = Comment::create([
                'content'           =>$content,
                'created_by'        =>$request->user()->id,
                'task_id'           =>$task_id,
                'type'              =>"ADD",
            ]);
        } else if($type === 2){

        }

    }

    // protected function checkInsideProject(Resquest $request);
    // protected function getROLE(Resquest $request);
    

}
