<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Task;
use Carbon\Carbon;
class CommentController extends Controller
{
    public function createComment (Request $request){
        $validator = Validator::make($request->all(), [
            'content'                =>  'required',   
        ]);
        if($validator->fails()){
            $error = $validator->errors()->all()[0];
            return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
        } else {
            $comment = Comment::create([
                'content'           =>$request->content,
                'created_by'        =>$request->user()->id,
                'task_id'           =>$request->task_id,
                'type'              =>"NORMAL",
            ]);

            // Lấy danh sách người đã từng comment vào task trước đó
        $task = Task::find($request->task_id);
        // $previousCommenters = $task->comments()->distinct()->pluck('created_by')->toArray();
        // // Gửi thông báo tới những người đã từng comment
        $previousCommenters = $task->comments()->distinct()->pluck('created_by')->toArray();
        $notifiedUsers = array_unique(array_merge($previousCommenters, [$task->assignee_id]));

        // Loại bỏ bản thân người comment và người tạo task
        $notifiedUsers = array_diff($notifiedUsers, [$request->user()->id]);

        foreach ($notifiedUsers as $commenter) {
            if($commenter == $task->getCommentsByProjectassignee_id) $this->makeNotification($commenter, $task->task_key, 3);
            else  $this->makeNotification($commenter, $task->task_key, 4);
        }
            return $this->jsonResponse('true', 'Comment created Successfully!', $notifiedUsers);
        }

    }
    public function editComment (Request $request, $id){
        $validator = Validator::make($request->all(), [
            'content'                =>  'required',   
        ]);
        if($validator->fails()){
            $error = $validator->errors()->all()[0];
            return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
        } else {
            $comment = Comment::find($id);
            if($comment->type == 'NORMAL'){
                $comment->content = $request->content;
                $comment->updated_at = $timestamp = Carbon::now();
            }
            $comment->save();
            return $this->jsonResponse('true', 'Comment edited Successfully!', $comment);
        }

    }

    public function show($id){
        $comment = Comment::findOrFail($id);
        return $this->jsonResponse('true', 'Comment gotten Successfully!', $comment);
    }

    public function getCommentsByTask(Request $request, $id){
        // $perPage = $request->input('per_page', 10);
        $comments = Comment::where('task_id', $id)->orderByDesc('id')->paginate(5);
        // $comments = Comment::where('task_id', $id)->get();
        $data = [
            'current_page' => $comments->currentPage(),
            'data' => $comments->items(),
            'per_page' => 5,
            'total' => $comments->total(),
            'current_page_url' => $comments->url($comments->currentPage())
        ];
        //thông báo cho nhiều người.

        return $this->jsonResponse('true', 'Comments of Task!', $data);
    }

    public function destroy($id){
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }

    public function getCommentsByProject($project_id)
    {
        $comments = Comment::with(['task:id,task_key,name', 'creator:id,name,avatar'])
            ->whereHas('task', function ($query) use ($project_id) {
                $query->where('project_id', $project_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

            return $this->jsonResponse(true, 'Get all Update!', $comments);

    }

    

    
}
