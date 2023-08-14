<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Notification;
use App\Enums\UserRole;


class TaskController extends Controller
{
    public function store(Request $request)
    {
        if($request->project_id && $this->checkInsideProject($request, $request->project_id)){
            $validator = Validator::make($request->all(), [
                'name'                  =>  'required|string',
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
                $taskCount = Task::where('project_id', $request->project_id)->count() + 1;
                $issue = Task::create([
                    'name'                  =>  $request->name,
                    'description'           =>  $request->description,   
                    'project_id'            =>  $request->project_id,
                    'milestone_id'          =>  $request->milestone_id,
                    'created_by'            =>  $request->user()->id,
                    'status'                =>  $request->status,
                    'priority'              =>  $request->priority,
                ]);
                if($request->estimate_time) $issue->estimate_time = $request->estimate_time;
                if($request->start_time) $issue->start_time = $request->start_time;
                if($request->end_time) $issue->end_time = $request->end_time;
                if($request->assignee_id) {
                    $issue->assignee_id = $request->assignee_id;     
                }
                    else $issue->assignee_id = $request->user()->id;
                $issue->task_key = $task_key .'-'. $taskCount;
                if($request->is_child && $request->parent_task_id){
                    $issue->is_child = true;
                    $issue->parent_task_id = $request->parent_task_id;
                    $issue->save();
                    $parentTask = Task::findOrFail($request->parent_task_id);
                    $parentTask->subTasks()->save($issue);
                }
                $issue->save();
                $this->autoMakeComment(1, $request->user()->id, $issue->id, "");
                if($issue->assignee_id && $issue->assignee_id != $request->user()->id) {
                    $this->makeNotification($issue->assignee_id, $issue->task_key, 1);
                }
                
                return $this->jsonResponse(true, 'Task created successfully!', $issue);    
            }

        }  else 
            return $this->jsonResponse(false, "Forbidden", [], 403);

    }
       // Lấy task cha cùng với task con
    public function getParentTaskWithSubTasks(Request $request, $taskId)
    {
        
        $task = Task::with('subTasks')->findOrFail($taskId);
        if($this->checkInsideProject($request, $task->project_id)){
            return $this->jsonResponse(true, "Task with SubTasks retrieved successfully", $task);
        } else 
            return $this->jsonResponse(false, "Forbidden", [], 403);
           
    }

    public function getTasksWithSubTasksInProject(Request $request, $projectId)
    {
        $tasks = Task::with('subTasks', 'assignee:id,name,avatar')
                        ->where('project_id', $projectId)
                        ->whereNull('parent_task_id')
                        ->orderBy('id', 'desc')
                        ->get();
        if($this->checkInsideProject($request, $task->project_id)){
            return response()->json([
                'status' => 'true',
                'message' => 'Tasks with SubTasks in Project retrieved successfully',
                'data' => $tasks,
            ]);
        }else 
            return $this->jsonResponse(false, "Forbidden", [], 403);
        


    }

    public function show(Request $request, $project_id)
    {
        if($this->checkInsideProject($request, $project_id)){
            $key = $request->input('key');
            $milestone_id = $request->input('milestone_id');
            $status = $request->input('status');
            $assignee_id = $request->input('assignee_id');

            $tasks = Task::query();

            // Thêm các điều kiện tìm kiếm vào query nếu có
            $tasks->where('project_id', $project_id);

            if ($key && !empty(trim($key))) {
                $tasks->where(function ($subquery) use ($key) {
                    $subquery->where('name', 'LIKE', "%$key%")
                             ->orWhere('task_key', 'LIKE', "%$key%");
                });
            }

            if ($milestone_id) {
                $tasks->where('milestone_id', $milestone_id);
            }

            if ($status) {
                if(strcmp($status,'Not_Closed') === 0){
                    $tasks->where('status', '<>', 'Closed');
                    
                } else $tasks->where('status', $status);
            }

            if ($assignee_id) {
                $tasks->where('assignee_id', $assignee_id);
            }

            $data = $tasks->whereNull('parent_task_id')->with('subTasks', 'assignee:id,name,avatar')->get();

            return $this->jsonResponse(true, 'Found All Task!', $data);
        } else 
            return $this->jsonResponse(false, "Forbidden", [], 403);
        
    }

    public function findById(Request $request, $key)
    {
        $tasks = Task::with('subTasks', 'assignee:id,name,avatar')->find($key);
        if($this->checkInsideProject($request, $tasks->project_id)){
            return $this->jsonResponse(true, 'Details of Task!', $tasks);
        } return $this->jsonResponse(false, "Forbidden", [], 403);

    }

    public function update(Request $request, $id)
    {
        $project_id = $request->project_id;
        $role = $request->user()->role;
        $user_id = $request->user()->id;
        
        if($project_id && $this->checkInsideProject($request, $project_id)){ 
            if(1){ 
                $validator = Validator::make($request->all(), [
                    'name'                  =>  'required|string',
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
                $issue = Task::with('subTasks', 'assignee:id,name,avatar')->find($id);
                
                if($issue) {
                    $oldValues = [
                        'name' => $issue->name,
                        'description' => $issue->description,
                        'milestone_id' => $issue->milestone_id,
                        'status' => $issue->status,
                        'priority' => $issue->priority,
                        // 'start_time' => $issue->start_time->format('Y-m-d'),
                        // 'end_time' => $issue->end_time->format('Y-m-d'),
                        'assignee_id' => $issue->assignee_id,
                        // 'estimate_time' => $issue->estimate_time,
                    ];

                    $issue->name = $request->name;
                    $issue->description = $request->description;
                    $issue->milestone_id = $request->milestone_id;
                    $issue->status = $request->status;
                    $issue->priority = $request->priority;
                    if($request->start_time) $issue->start_time = $request->start_time;
                    if($request->end_time) $issue->end_time = $request->end_time;
                    //check có thuộc project không?

                    if($request->assignee_id && $request->assignee_id !== $issue->assignee_id && $request->assignee_id != $user_id) {
                        $issue->assignee_id = $request->assignee_id;
                        $this->makeNotification($issue->assignee_id, $issue->task_key, 1);
                    }
                    if($request->estimate_time) $issue->estimate_time = $request->estimate_time;
                    $issue->update();
                    // if($request->assignee_id && $request->assignee_id != $issue->assignee_id)  $issue->refresh();
                    $issue = Task::with('subTasks', 'assignee:id,name,avatar')->find($id);
                    // thông báo cho nhiều người.
                    if($request->assignee_id != $user_id) $this->makeNotification($issue->assignee_id, $issue->task_key, 2);
                    //make log
                    $newValues = $request->all();
                    $logChanges = [];

                    foreach ($oldValues as $field => $oldValue) {
                        if ($oldValue !== $newValues[$field]) {
                            $logChanges[] = "[{$field}: {$oldValue} -> {$newValues[$field]}]";
                        }
                    }

                    if (!empty($logChanges)) {
                        $logMessage = implode("\n", $logChanges);
                        $taskLog = new Comment([
                            'task_id' => $issue->id,
                            'content' => "Updated: {$logMessage}",
                            'created_by' =>$request->user()->id,
                            'type'  => "UPDATE"
                        ]);

                        $taskLog->save();
                    }
                    //end make log
                    return $this->jsonResponse('true', 'Task Updated Successfully!', $issue);
                }
                    return response()->json(['status'=>'false', 'message'=>'Task not found!', 'data'=>[]], 404);
            }
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    }
    }

    public function dashBoardTask(Request $request)
{
    $user_id = $request->user()->id;
    $query = Task::query();

    if ($request->input('created')) {
        $query->where('created_by', $user_id);
    } else{
        $query->where('assignee_id', $user_id);
    }


    if ($request->input('all')) {
    } else {
        // Mặc định là chỉ lấy task có end_time >= ngày hôm nay
        // $query->where('end_time', '>=', Carbon::now());
        if ($request->input('due_today')) {
            $query->whereDate('end_time', '=', Carbon::today());
        }
        
        if ($request->input('overdue')) {
            $query->where('end_time', '<', Carbon::now());
        }
    }

    $tasks = $query->get();

    return $this->jsonResponse(true, "Tasks in dashboard!", $tasks);
}

    public function destroy(Request $request, $id)
    {
        //check quyền ở đây
        // PM: có thể làm hết, nhưng với Member chỉ có thể xóa subtassk thôi.
        $task = Task::findOrFail($id);
        $project_id = $task->project_id;
        if($this->checkInsideProject($request, $project_id)){
            if ($task->is_child) {
                // Xóa task con và comment của task con
                $task->delete();
                $task->comments()->delete();
            } else if(!$task->is_child && $request->user()->role == UserrOLE::PM){
                // Xóa tất cả các sub task và comment của task cùng với các sub task
                $task->subtasks()->delete();
                $commentIds = $task->comments->pluck('id')->toArray();
                $task->comments()->delete();
                Comment::whereIn('task_id', $commentIds)->delete();
                $task->delete();
            }
            return $this->jsonResponse(true, "Task ". $task->task_key. " and related data deleted successfully",[]);
        } else 
        return $this->jsonResponse(false, "Forbidden", [], 403); 
    }

}
