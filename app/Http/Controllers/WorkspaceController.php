<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\Comment;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class WorkspaceController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'          =>      'required|string|unique:workspaces,name,except,id',
            'organization'  =>      'required|string',
            'domain'        =>      'required|string'
        ]);

        $workspace = Workspace::create([
            'name'          =>          $data['name'],
            'organization_name'  =>     $data['organization'],
            'domain'        =>          $data['domain'],
            'secret_code'   =>          $data['secret_code'],
            'secret_key'    =>          $secret_key,
            'description'   =>          $request->description,
            'workspace_admin_id'    =>  $request->user()->id   
        ]);

        $admin = User::find($request->user()->id);
        $admin->workspace_id = $workspace->id;
        $admin->save();
        return response()->json(['status'=>'true', 'message'=>'Workspace Created!', 'data'=>$workspace]);

    }

    //Get details of a workspace
    public function show(Request $request, $id)
    {
        if($request->user()->workspace_id == $id){
            $data = Workspace::find($id);
            return $this->jsonResponse(true, "Details of Workspace!", $data);
        } else 
            return $this->jsonResponse(false, "Forbidden", [], 403);
    }

    public function edit(Request $request, $id)
    {
        if($request->user()->workspace_id == $id && $request->user()->role ==  UserRole::WORKSPACE_ADMIN){
            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string',
                'organization_name'   =>  'required',
                'domain'              =>  'required|string',
                'description'         =>  'required',   
            ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return $this->jsonResponse(false, $error, [], 422);
            } else {
                $workspace = Workspace::find($id);
                if($workspace){
                    $workspace->name = $request->name;
                    $workspace->organization_name = $request->organization_name;
                    $workspace->domain = $request->domain;
                    $workspace->description = $request->description;
                    // $old_path = $workspace->avatar;
                //     if(strcmp($request->avatar, $old_path) === 0  && $request->avatar->isValid()){
                //         $file_name = $user->id.'.'.$request->avatar->extension();
                //         $request->file('avatar')->storeAs('public/images/avatars', $file_name );
                //         $path = "images/avatars/$file_name ";
                //         $user->avatar = $path;
                // }
                if($request->avatar && strcmp($request->avatar, $workspace->avatar) !== 0)  $workspace->avatar = $request->avatar;
                $workspace->update();
                return $this->jsonResponse(true, "Workspace updated successfully!", $workspace);
                } else 
                    return $this->jsonResponse(false, "Workspace not found!", [], 404);

            }

        } else 
            return $this->jsonResponse(false, "Forbidden", [], 403);
    }

    public function getProjectsByWorkspace(Request $request, $id)
    {
        if($request->user()->workspace_id == $id){
            $workspace = Workspace::findOrFail($id);
            if($workspace && $workspace->projects) 
                return response()->json(['status'=>'true', 'message'=>'Projects of Workspace', 'data'=>$workspace->projects]);
        } else 
        return $this->jsonResponse(false, 'Forbidden' ,[], 403);
    }

    public function getMembersByWorkspace(Request $request, $id)
    {

        if($request->user()->workspace_id == $id){
            $key = $request->input('key');
            $role = $request->input('role');

            $listMembers = User::query()
            ->when($id, function ($query, $id) {
                return $query->where('workspace_id', $id);
            })
            ->when($key, function ($query, $key) {
                return $query->where(function ($query) use ($key) {
                    $query->where('name', 'like', "%$key%")
                        ->orWhere('email', 'like', "%$key%");
                });
            })
            ->when($role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->get();

            return $this->jsonResponse(true, 'Members of Workspace 123!', $listMembers);
        } else 
            return $this->jsonResponse('false', 'Forbidden', [], 403);

    }

    public function getCommentsByWorkspace(Request $request)
    {
        $workspaceId = $request->user()->workspace_id;
        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return response()->json(['message' => 'Workspace not found'], 404);
        }

         $comments = Comment::whereIn('task_id', function ($query) use ($workspaceId) {
            $query->select('id')
                ->from('tasks')
                ->whereIn('project_id', function ($query) use ($workspaceId) {
                    $query->select('id')
                        ->from('projects')
                        ->where('workspace_id', $workspaceId);
                });
        })
        ->orderBy('id', 'desc')
        ->get();
        return response()->json(['comments' => $comments]);
    }

    public function destroy($id)
    {
        //
    }
}
