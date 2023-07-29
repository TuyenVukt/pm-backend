<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\Project;


class TaskController extends Controller
{

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

    $validator = Validator::make($request->all(), [
        'name'                  =>  'required|string|unique:tasks,name',
        'description'           =>  'required',   
        'start_time'            =>  'nullable',
        'end_time'              =>  'nullable',
        'project_id'            =>  'required',
        'milestone_id'          =>  'required',
        'status'                =>  'required',
        'priority'              =>  'required',
    ]);

    if($validator->fails()){
        $error = $validator->errors()->all()[0];
        return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
    } else {
        $project = Project::find($request->project_id);
        $task_key = $project->project_key;
        $issue = Task::create([
            'name'                  =>  $request->name,
            'description'           =>  $request->description,   
            'project_id'            =>  $request->project_id,
            'milestone_id'          =>  $request->milestone_id,
            'created_by'            =>  $request->user()->id,
            'status'                =>  $request->status,
            'priority'              =>  $request->priority,
        ]);

        if($request->start_time) $issue->start_time = $request->start_time;
        if($request->end_time) $issue->end_time = $request->end_time;
        if($request->asignee_id) $issue->asignee_id = $request->asignee_id;
        $issue->task_key = $task_key .'-'. $issue->id;
        $issue->save();

        return response()->json(['status'=>'true', 'message'=>'Issue Created!', 'data'=>$issue]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        //kiem tra xem user co thuoc project hay khong?
        $issue = Task::firstWhere('task_key', $key);
        if($issue) return response()->json(['status'=>'true', 'message'=>'Details of Issue', 'data'=>$issue]);
    }

    public function findById($key)
    {
        //kiem tra xem user co thuoc project hay khong?
        $issue = Task::findOrFail($key);
        if($issue) return response()->json(['status'=>'true', 'message'=>'Details of Issue', 'data'=>$issue]);
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
        //kiểm tra xem user này có quyền update không ()
        //bắt buộc: title, descripton,project_id, milestone_id, status, category, priority
        //start_time và end_time, assign_id thì có thể thêm sau.

        if(1){ 
            if(1){ 
                $validator = Validator::make($request->all(), [
                    'name'                  =>  'required|string',
                    'description'           =>  'required',   
                    'start_time'            =>  'nullable',
                    'end_time'              =>  'nullable',
                    'project_id'            =>  'required',
                    'milestone_id'          =>  'required',
                    'status'                =>  'required',
                    'priority'              =>  'required',
                ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $issue = Task::find($id);
                if($issue){
                    $issue->name = $request->name;
                    $issue->description = $request->description;
                    $issue->milestone_id = $request->milestone_id;
                    $issue->status = $request->status;
                    $issue->priority = $request->priority;
                    if($request->start_time) $project->start_date = $request->start_date;
                    if($request->end_time) $project->end_time = $request->end_time;
                    //check có thuộc project không?

                    if($request->asignee_id) $issue->asignee_id = $request->asignee_id;
                    if($request->estimate_time && $request->is_day) {
                        $issue->estimate_time = $request->estimate_time;
                        $issue->is_day = $request->is_day;
                    }
                    
                    $issue->update();
                    return $this->jsonResponse('true', 'Task Updated Successfully!', $issue);
                }
                if(is_null($workspace)) 
                    return response()->json(['status'=>'false', 'message'=>'Workspace not found!', 'data'=>[]], 404);
            }
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    }
    }

    // public function update(Request $request, $id)
    // {
    //     //kiểm tra xem user này có quyền update không ()
    //     //bắt buộc: title, descripton,project_id, milestone_id, status, category, priority
    //     //start_time và end_time, assign_id thì có thể thêm sau.

    //     if(1){ 
    //         if(1){ 
    //             $validator = Validator::make($request->all(), [
    //                 'title'                 =>  'required|string',
    //                 'description'           =>  'required',   
    //                 'start_time'            =>  'nullable',
    //                 'end_time'              =>  'nullable',
    //                 'milestone_id'          =>  'required',
    //                 'status'                =>  'required',
    //                 'category'              =>  'required',
    //                 'priority'              =>  'required',  
    //             ]);

    //         if($validator->fails()){
    //             $error = $validator->errors()->all()[0];
    //             return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
    //         } else {
    //             $issue = Issue::find($id);
    //             if($issue){
    //                 $issue->title = $request->title;
    //                 $issue->description = $request->description;
    //                 $issue->milestone_id = $request->milestone_id;
    //                 $issue->status = $request->status;
    //                 $issue->category = $request->category;
    //                 $issue->priority = $request->priority;
    //                 if($request->start_time) $project->start_date = $request->start_date;
    //                 if($request->end_time) $project->end_time = $request->end_time;
    //                 //check có thuộc project không?
    //                 if($request->asignee_id) $issue->asignee_id = $request->asignee_id;

    //                 $issue->update();
    //                 return response()->json(['status'=>'true', 'message'=>'Issue Updated!', 'data'=>$issue]);
    //             }
    //             if(is_null($workspace)) 
    //                 return response()->json(['status'=>'false', 'message'=>'Workspace not found!', 'data'=>[]], 404);
    //         }
    //     } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    // }

    // }

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
