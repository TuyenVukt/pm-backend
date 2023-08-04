<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Comment;
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

            return $this->jsonResponse('true', 'Comment created Successfully!', $comment);
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
            return $this->jsonResponse('true', 'Comment edited Successfully!', $comment);
        }

    }

    public function show($id){
        $comment = Comment::findOrFail($id);
        return $this->jsonResponse('true', 'Comment gotten Successfully!', $comment);
    }

    public function getCommentsByTask(Request $request, $id){
        // $perPage = $request->input('per_page', 10);
        $comments = Comment::where('task_id', $id)->paginate(5);
        // $comments = Comment::where('task_id', $id)->get();
        $data = [
            'current_page' => $comments->currentPage(),
            'data' => $comments->items(),
            'next_page_url' => $comments->nextPageUrl(),
            'per_page' => 5,
            'total' => $comments->total(),
        ];
        return $this->jsonResponse('true', 'Comments of Task!', $data);

    }

    
}
