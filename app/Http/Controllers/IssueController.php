<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Issue;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    //	id	bigint UN AI PK//
	// title	varchar(255)//
	// description	text//
	// start_time	date//
	// end_time	date//
	// project_id	bigint UN//
	// milestone_id	bigint UN//
	// before_task_id	int UN
	// after_task_id	int UN
	// created_user_id	bigint UN//
	// asignee_id	bigint UN//
	// status	varchar(255)//
	// category_id	varchar(255)//
	// priority	varchar(255)//
	// is_parent	tinyint(1)
	// is_child	tinyint(1)

    //LOW MIDDLE HIGH
    //TASK REQUEST BUG OTHER


    $validator = Validator::make($request->all(), [
        'title'                 =>  'required|string|unique:issues,title,except,id',
        'description'           =>  'required',   
        'start_time'            =>  'nullable|date_format:Y-m-d',
        'end_time'              =>  'nullable|date_format:Y-m-d',
        'project_id'            =>  'required',
        'milestone_id'          =>  'required',
        'status'                =>  'required',
        'category'              =>  'required',
        'priority'              =>  'required',

    ]);

    if($validator->fails()){
        $error = $validator->errors()->all()[0];
        return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
    } else {
        
        $issue = Issue::create([
            'title'                 =>  $request->title,
            'description'           =>  $request->description,   
            'start_time'            =>  $request->start_time,
            'project_id'            =>  $request->project_id,
            'milestone_id'          =>  $request->milestone_id,
            'created_user_id'       =>  $request->user()->id,
            'status'                =>  $request->status,
            'category'              =>  $request->category,
            'priority'              =>  $request->priority,
        ]);

        if($request->start_time) $issue->start_time = $request->start_time;
        if($request->end_time) $issue->end_time = $request->end_time;
        if($request->asignee_id) $issue->asignee_id = $request->asignee_id;

        return response()->json(['status'=>'true', 'message'=>'Issue Created!', 'data'=>$issue]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(1){ 

            if(1){ 
                $validator = Validator::make($request->all(), [
                    'title'                 =>  'required|string',
                    'description'           =>  'required',   
                    'start_time'            =>  'nullable|date_format:Y-m-d',
                    'end_time'              =>  'nullable|date_format:Y-m-d',
                    'project_id'            =>  'required',
                    'milestone_id'          =>  'required',
                    'status'                =>  'required',
                    'category'              =>  'required',
                    'priority'              =>  'required',
            
                ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $project = Project::find($id);

                if(($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM) && $project ){
                    $project->name = $request->name;
                    $project->description = $request->description;
                    $project->start_date = $request->start_date;
                    if($request->due_date) $project->due_date = $request->due_date;
                    // $old_path = $workspace->avatar;
                //     if(strcmp($request->avatar, $old_path) === 0  && $request->avatar->isValid()){
                //         $file_name = $user->id.'.'.$request->avatar->extension();
                //         $request->file('avatar')->storeAs('public/images/avatars', $file_name );
                //         $path = "images/avatars/$file_name ";
                //         $user->avatar = $path;
                // }
                    $project->update();
                    // $workspace->makeHidden(['secret_code', 'secret_key', 'workspace_admin_id']);
                    return response()->json(['status'=>'true', 'message'=>'Project Updated!', 'data'=>$project]);
                }
                if($request->user()->role !== UserRole::WORKSPACE_ADMIN || $request->user()->workspace_id = $workspace->id) 
                    return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
                if(is_null($workspace)) 
                    return response()->json(['status'=>'false', 'message'=>'Workspace not found!', 'data'=>[]], 404);

            }

            
            return response()->json(['status'=>'true', 'message'=>'Workspace Edited!', 'data'=>$workspace]);
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $project = Project::find($id);

                if(($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM) && $project ){
                    $project->name = $request->name;
                    $project->description = $request->description;
                    $project->start_date = $request->start_date;
                    if($request->due_date) $project->due_date = $request->due_date;

                    $project->update();
                    // $workspace->makeHidden(['secret_code', 'secret_key', 'workspace_admin_id']);
                    return response()->json(['status'=>'true', 'message'=>'Project Updated!', 'data'=>$project]);
                }
                if($request->user()->role !== UserRole::WORKSPACE_ADMIN || $request->user()->workspace_id = $workspace->id) 
                    return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
                if(is_null($workspace)) 
                    return response()->json(['status'=>'false', 'message'=>'Workspace not found!', 'data'=>[]], 404);

            }

            
            return response()->json(['status'=>'true', 'message'=>'Workspace Edited!', 'data'=>$workspace]);
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
