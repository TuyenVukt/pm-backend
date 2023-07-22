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
    public function create(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string|unique:projects,name,except,id',
                'project_key'         =>  'required',
                'description'         =>  'required',   
            ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                if(($request->user()->role === UserRole::WORKSPACE_ADMIN || $request->user()->role === UserRole::PM)){
                    $project = Project::create([
                        'name'            =>  $request->name,
                        'project_key'     =>  $request->project_key,
                        'description'     =>  $request->description,
                        'start_date'      =>  $request->start_date ? $request->start_date : Carbon::now()->format('Y-m-d H:i:s'),
                        'workspace_id'    =>  $request->user()->workspace_id
                    ]);
                    
                    return response()->json(['status'=>'true', 'message'=>'Project Created!', 'data'=>$project]);
                } else
                    return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
            }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //kiem tra xem 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //kiem tra xem user co la workspace_admin, pm hay member, tuc la kiem tra user có thuộc project hay không
        $project = Project::findOrFail($id);
        return response()->json(['status'=>'true', 'message'=>'Details of Project', 'data'=>$project]);


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
        //Check quyền: Workspace_admin hoặc PM và phải thuộc Project đó. 
        if(1){ 

            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string',
                'description'         =>   'required',  
                'start_date'          =>  'required|date_format:Y-m-d', 
                'due_date'            =>  'nullable|date_format:Y-m-d'
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

    public function addMemberToProject(Request $request){

        try{
    
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
            $project = Project::find($request->input('project_id'));
            if(!$project) return response()->json(['status'=>'false', 'message'=>'Project not found!', 'data'=>[]], 404);

            $listUsersId = $request->list_users_id;
            $collection = collect([]);
            foreach($listUsersId as $item){
                $user = User::find($item);
                // if($user) $collection->push($user);
                if($user) {
                    $project->users()->attach($user);
                }
            }
            
            // return $collection;
            // return $project->users;
            return response()->json(['status'=>'true', 'message'=>'List of Members added!', 'data'=>$project->users]);
            
            // //nếu 1 trong 2 biến trên không tồn tại => 404
            // // Attach the member to the project using the "attach" method on the pivot relationship
            // $project->users()->attach($user);
            // return $user->projects;

        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Can not add member to project!', 'data'=>[]], 500);
        }
        
    }

    public function getAllMembersOfProject($id){
        //kiem tra xem user co la workspace_admin, pm hay member, tuc la kiem tra user có thuộc project hay không
        $project = Project::findOrFail($id);
        return $project->users;

    }
}
