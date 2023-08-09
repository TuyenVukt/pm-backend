<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Project;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Carbon;


class ProjectController extends Controller
{
    //create a project 
    public function create(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string',
                'project_key'         =>  'required|string',
                'description'         =>  'required',   
            ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                if($request->user()->role === UserRole::WORKSPACE_ADMIN){
                    $project = Project::create([
                        'name'            =>  $request->name,
                        'project_key'     =>  $request->project_key,
                        'description'     =>  $request->description,
                        'start_date'      =>  $request->start_date ? $request->start_date : Carbon::now()->format('Y-m-d'),
                        'due_date'        =>  $request->due_date ? $request->due_date : Carbon::now()->format('Y-m-d'),
                        'workspace_id'    =>  $request->user()->workspace_id
                    ]);
                    $project->users()->attach($request->user());
                    
                    return $this->jsonResponse(true, 'Project created successfully!', $project);
                } else
                    return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
            }

    }

    public function show(Request $request, $id)
    {
        if($this->checkInsideProject($request, $id)){
            $project = Project::findOrFail($id);
            return $this->jsonResponse(true, 'Details of Project', $project);
        } else 
        return $this->jsonResponse(false, 'Forbidden' ,[], 403);
    }

    public function update(Request $request, $id)
    {
            if((($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM) ) && $this->checkInsideProject($request, $id)){
                $validator = Validator::make($request->all(), [
                    'name'                =>  'required|string',
                    'description'         =>   'required',  
                    'start_date'          =>  'required', 
                    'due_date'            =>  'nullable'
                ]);
    
                if($validator->fails()){
                    $error = $validator->errors()->all()[0];
                    return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
                } else {
                    $project = Project::find($id);
    
                    if($project){
                        $project->name = $request->name;
                        $project->description = $request->description;
                        $project->start_date = $request->start_date;
                        if($request->due_date) $project->due_date = $request->due_date;
                        $project->update();
                        return $this->jsonResponse(true, 'Project Updated successfully!', $project);
                    } else 
                        return $this->jsonResponse(false, 'Project not found!',[], 404);
                }
    
            }else 
            return $this->jsonResponse(false, 'Forbidden' ,[], 403);
            
    }

    public function addMemberToProject(Request $request){
        try{
            if((($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM) ) && $this->checkInsideProject($request, $id)){
                
            }
            $user = User::find($request->input('user_id'));
            
            $project = Project::find($request->input('project_id'));
            // //nếu 1 trong 2 biến trên không tồn tại => 404
            // // Attach the member to the project using the "attach" method on the pivot relationship
            if(!$user && !$project)
            $project->users()->attach($user);
            return $user->projects;

        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Can not add member to project!', 'data'=>[]], 500);
        }
        
    }

    public function addListMembersToProject(Request $request){

        try{
            if((($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM) )){
                $project = Project::find($request->input('project_id'));
                if(!$project) return response()->json(['status'=>'false', 'message'=>'Project not found!', 'data'=>[]], 404);
    
                $listUsersId = $request->list_users_id;
                $collection = collect([]);
                foreach($listUsersId as $item){
                    $user = User::find($item);
                    if($user) {
                        $project->users()->attach($user);
                    }
                }
                
                return response()->json(['status'=>'true', 'message'=>'List of Members added!', 'data'=>$project->users]);
            }
            
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>$e, 'data'=>[]], 500);
        }
        
    }

    public function getAllMembersOfProject(Request $request, $id){
        if($this->checkInsideProject($request, $id)){
            $project = Project::findOrFail($id);
            return $project->users;
        }


    }

    public function getProjectsByUserId(Request $request){
        $key = $request->input('key');
            $user = User::findOrFail($request->user()->id);
            if(!empty(trim($key))) $projects = $user->projects()->where('name', 'LIKE', "%$key%")->get();
            else $projects = $user->projects;
            return $this->jsonResponse('true', 'List Projects By User!',$projects);
    }

    public function getUsersNotInProject(Request $request, $project_id)
    {
        if($this->checkInsideProject($request, $project_id)){
            $project = Project::findOrFail($project_id);
            $allUsers = User::where('workspace_id', $request->user()->workspace_id)->get();
            // Lấy danh sách các user đang thuộc về project
            $usersInProject = $project->users;
            // Lọc các user không thuộc về project
            $usersNotInProject = $allUsers->diff($usersInProject);

            return $this->jsonResponse('true', 'List Projects By User!',$usersNotInProject);
        } else 
            return $this->jsonResponse(false, 'Forbidden' ,[], 403);
    }

    public function checkUser(Request $request, $project_id){
        return $this->checkInsideProject($request, $project_id);
    }
}
