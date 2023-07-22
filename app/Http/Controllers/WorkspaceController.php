<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class WorkspaceController extends Controller
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
        //name, organization, avatar, domain, secret_code
        $data = $request->validate([
            'name'          =>      'required|string|unique:workspaces,name,except,id',
            'organization'  =>      'required|string',
            'domain'        =>      'required|string',
            'secret_code'   =>      'required|string' 
        ]);

        $key = $data['domain'] . $data['secret_code'];
        $secret_key = bcrypt($key);


        $workspace = Workspace::create([
            'name'          =>          $data['name'],
            'organization_name'  =>          $data['organization'],
            'domain'        =>          $data['domain'],
            'secret_code'   =>          $data['secret_code'],
            'secret_key'    =>          $secret_key,
            'description'   =>          $request->description,
            'workspace_admin_id'    =>  $request->user()->id   
        ]);

        // $workspace->makeHidden(['secret_code', 'secret_key', 'workspace_admin_id']);

        $admin = User::find($request->user()->id);
        $admin->workspace_id = $workspace->id;
        $admin->save();
        return response()->json(['status'=>'true', 'message'=>'Workspace Created!', 'data'=>$workspace]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if($request->user()->workspace_id == $id){
            $data = Workspace::find($id);
            // $data->makeHidden(['secret_code', 'secret_key', 'workspace_admin_id']);
            // $user = User::select('id', 'name')->where('role', '=', 2)->get();
            return response()->json(['status'=>'true', 'message'=>'Details of Workspace', 'data'=>$data]);
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if($request->user()->workspace_id == $id){

            $validator = Validator::make($request->all(), [
                'name'                =>  'required|string|unique:workspaces,name,except,id',
                'organization_name'   =>  'required',
                'domain'              =>  'required|string',
                'description'         =>   'required',   
            ]);

            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $workspace = Workspace::find($id);

                if($request->user()->role === UserRole::WORKSPACE_ADMIN && $workspace && $request->user()->workspace_id === $workspace->id ){
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
                    $workspace->update();
                    // $workspace->makeHidden(['secret_code', 'secret_key', 'workspace_admin_id']);
                    return response()->json(['status'=>'true', 'message'=>'Workspace Updated!', 'data'=>$workspace]);
                }
                if($request->user()->role !== UserRole::WORKSPACE_ADMIN || $request->user()->workspace_id = $workspace->id) 
                    return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
                if(is_null($workspace)) 
                    return response()->json(['status'=>'false', 'message'=>'Workspace not found!', 'data'=>[]], 404);

            }

            
            return response()->json(['status'=>'true', 'message'=>'Workspace Edited!', 'data'=>$workspace]);
        } else return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
    }

    public function getProjectsByWorkspace(Request $request, $id)
    {
        if($request->user()->workspace_id == $id){
            $workspace = Workspace::findOrFail($id);
            if($workspace && $workspace->projects) 
                return response()->json(['status'=>'true', 'message'=>'Projects of Workspace', 'data'=>$workspace->projects]);
                return response()->json(['status'=>'true', 'message'=>'Projects of Workspace', 'data'=>[]]);
        } else 
            return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
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
        //
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
