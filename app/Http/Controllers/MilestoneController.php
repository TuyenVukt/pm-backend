<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Milestone;
use App\Models\Project;
use App\Enums\UserRole;

class MilestoneController extends Controller
{

    public function store(Request $request){  
        if ($request->user()->role ==  UserRole::PM && $this->checkInsideProject($request, $request->project_id)){
            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string',
                'description'          =>  'nullable',   
                'start_date'           =>  'nullable|date_format:Y-m-d', 
                'due_date'             =>  'nullable|date_format:Y-m-d',
                'project_id'           =>  'required',  
        ]);
        if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
        } else {
            $milestone = Milestone::create([
                'name'             => $request->name,
                'description'       => $request->description,
                'start_date'      =>  $request->start_date ? $request->start_date : Carbon::now()->format('Y-m-d'),
                'due_date'        =>  $request->due_date ? $request->due_date : Carbon::now()->format('Y-m-d'),
                'project_id'        => $request->project_id,
                'created_by'        => $request->user()->id,
            ]);
                
            return response()->json(['status'=>'true', 'message'=>'Milestone Created!', 'data'=>$milestone]);
        }
        } else 
            return $this->jsonResponse(false, 'Forbidden' ,[], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id){
        $milestone = Milestone::find($id);
        if($milestone && $this->checkInsideProject($request, $milestone->project_id))
            return $this->jsonResponse(true, "Details of a milestone", $milestone);
        return $this->jsonResponse(false, "Forbidden", [], 403);
    }

    public function getMilestoneByProject(Request $request, $id){
        try{
            if($this->checkInsideProject($request, $id)){
                $project = Project::findOrFail($id);
                $milestones = $project->milestones;
                return $this->jsonResponse(true, "Milestone of a Project!", $milestones);
            }
            return $this->jsonResponse(false, "Forbidden", [], 403);
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Get milestones failed!', 'data'=>[]], 500);
        }
        
    }

    public function update(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string',
                'description'         =>   'nullable',  
                'start_date'          =>  'nullable', 
                'due_date'            =>  'nullable'
            ]);
            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $milestone = Milestone::findOrFail($id);
                if($this->checkInsideProject($request, $milestone->project_id) && $request->user()->role == UserRole::PM){
                    $milestone->name = $request->name;
                    $milestone->description = $request->description;
                    $milestone->start_date = $request->start_date ? $request->start_date : Carbon::now()->format('Y-m-d');
                    $milestone->due_date = $request->due_date ? $request->due_date : Carbon::now()->format('Y-m-d');

                    $milestone->update();
                    return $this->jsonResponse(true, "Milestone updated successfully!", $milestone);
                }
                return $this->jsonResponse(false, "Forbidden", [], 403);
            }
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Get milestones failed!', 'data'=>[]], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $milestone = Milestone::with('tasks.subtasks.comments')->find($id);
        if($this->checkInsideProject($request, $milestone->project_id) && $request->user()->role == UserRole::PM){

            if (!$milestone) {
                return response()->json(['message' => 'Milestone not found'], 404);
            }
    
            // Xóa các comment của tất cả subtask của tất cả task của milestone
            foreach ($milestone->tasks as $task) {
                foreach ($task->subtasks as $subtask) {
                    $subtask->comments()->delete();
                }
            }
    
            // Xóa tất cả subtask của tất cả task của milestone
            foreach ($milestone->tasks as $task) {
                $task->subtasks()->delete();
            }
    
            // Xóa tất cả task của milestone
            $milestone->tasks()->delete();
    
            // Xóa milestone
            $milestone->delete();
    
            return $this->jsonResponse(true, "This Milestone and related data deleted successfully",[]);}

        return $this->jsonResponse(false, "Forbidden", [], 403);
       
    }
}
