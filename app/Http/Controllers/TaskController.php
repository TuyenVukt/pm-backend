<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;


class TaskController extends Controller
{

    public function create()
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  =>  'required|string|unique:tasks,name',
            'description'           =>  'required',   
            'start_time'            =>  'nullable',
            'end_time'              =>  'nullable',
            'project_id'            =>  'required',
            'milestone_id'          =>  'nullable',
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
            if($request->assignee_id) $issue->assignee_id = $request->assignee_id;
                else $issue->assignee_id = $request->user()->id;
            $issue->task_key = $task_key .'-'. $issue->id;
            if($request->is_child && $request->parent_task_id){
                $issue->is_child = true;
                $issue->parent_task_id = $request->parent_task_id;
                $issue->save();
                $parentTask = Task::findOrFail($request->parent_task_id);
                $parentTask->subTasks()->save($issue);
            }
            $issue->save();
            return response()->json(['status'=>'true', 'message'=>'Task Created!', 'data'=>$issue]);

        }
    }
       // Lấy task cha cùng với task con
    public function getParentTaskWithSubTasks($taskId)
    {
           $task = Task::with('subTasks')->findOrFail($taskId);
   
           return response()->json([
               'status' => 'true',
               'message' => 'Task with SubTasks retrieved successfully',
               'data' => $task,
           ]);
    }

    public function getTasksWithSubTasksInProject($projectId)
    {
        $tasks = Task::with('subTasks', 'assignee:id,name,avatar')
            ->where('project_id', $projectId)
            ->whereNull('parent_task_id')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'true',
            'message' => 'Tasks with SubTasks in Project retrieved successfully',
            'data' => $tasks,
        ]);
    }

    public function show(Request $request)
    {
        $key = $request->input('key');
        $milestone = $request->input('milestone');
        $status = $request->input('status');
        $assignee = $request->input('assignee');

        $tasks = Task::query();

        // Thêm các điều kiện tìm kiếm vào query nếu có
        if ($name) {
            $tasks->where('name', 'like', '%' . $name . '%');
        }

        if ($milestone) {
            $tasks->where('milestone', 'like', '%' . $milestone . '%');
        }

        if ($status) {
            $tasks->where('status', $status);
        }

        if ($assignee) {
            $tasks->where('assignee', $assignee);
        }

        // Lấy danh sách các task đã tìm thấy
        $tasks = $tasks->get();

        return response()->json(['tasks' => $tasks], 200);
    }

    public function findById($key)
    {
        $tasks = Task::with('subTasks', 'assignee:id,name,avatar')->find($key);
        return response()->json([
            'status' => 'true',
            'message' => 'Details of Task',
            'data' => $tasks,
        ]);
    }

    public function edit($id)
    {
        //
    }

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
                    // 'project_id'            =>  'required',
                    'milestone_id'          =>  'nullable',
                    'status'                =>  'required',
                    'priority'              =>  'required',
                ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $issue = Task::with('subTasks', 'assignee:id,name,avatar')->find($id);
                if($issue){
                    $issue->name = $request->name;
                    $issue->description = $request->description;
                    $issue->milestone_id = $request->milestone_id;
                    $issue->status = $request->status;
                    $issue->priority = $request->priority;
                    if($request->start_time) $issue->start_time = $request->start_time;
                    if($request->end_time) $issue->end_time = $request->end_time;
                    //check có thuộc project không?

                    if($request->assignee_id && $request->assignee_id !== $issue->assignee_id) {
                        $issue->assignee_id = $request->assignee_id;
                        $assignee = User::findOrFail($request->assignee_id);
                    }
                    if($request->estimate_time) $issue->estimate_time = $request->estimate_time;
                    $issue->update();
                    // if($request->assignee_id && $request->assignee_id != $issue->assignee_id)  $issue->refresh();
                    $issue = Task::with('subTasks', 'assignee:id,name,avatar')->find($id);
                    
                    return $this->jsonResponse('true', 'Task Updated Successfully!', $issue);
                }
                    return response()->json(['status'=>'false', 'message'=>'Task not found!', 'data'=>[]], 404);
            }
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    }
    }

    public function destroy($id)
    {
        //
    }
}
