<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Enums\UserRole;

class UserController extends Controller
{
    private const DEFAULT_AVATAR = 'public/images/avatars/default.jpg';

    // public function getAllUserInWorkspace(Request $request)
    // {
    //     //check nếu $request->secret_code = workspace_code thì tiếp tục.
    //     return User::select('id', 'name', 'email', 'avatar', 'is_active', 'role', 'created_at', 'updated_at')
    //         ->where('workspace_id', '=', $request->input('id'))
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(10, ['*'], 'page', $request->input('page'));

    // }

    public function getProfile(Request $request){
        try{
            $user_id = $request->user()->id;
            $data  = User::find($user_id);
            return $data;
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>$e->getMessage(), 'data'=>[]], 500);
        }
    }

    public function getMemberById(Request $request, $id){
        try{
            $data  = User::find($id);
            if($request->user()->workspace_id == $data->workspace_id) 
                return response()->json(['status'=>'true', 'message'=>"Profile gotten successfully  ", 'data'=>$data]); 
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>$e->getMessage(), 'data'=>[]], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'          =>  'required',
                'email'         =>  'required|email|unique:users,id',
                'avatar'        =>  'nullable'
            ]);
            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {
                $user = User::find($request->user()->id);
                $user->name = $request->name;
                $user->email = $request->email;
                // if($request->avatar && $request->avatar->isValid()){
                //     $file_name = $user->id.'.'.$request->avatar->extension();
                //     $request->file('avatar')->storeAs('public/images/avatars', $file_name );
                //     $path = "public/images/avatars/$file_name ";
                //     $old_path = $user->avatar;
                //     $user->avatar = $path;
                //     $user->update();

                //     return response()->json(['status'=>'true', 'message'=>'Profile Updated!', 'data'=>$user]);
                // }
                if($request->avatar && strcmp($request->avatar, $user->avatar) !== 0) 
                        $user->avatar = $request->avatar;
                $user->update();

                return response()->json(['status'=>'true', 'message'=>'Profile Updated!', 'data'=>$user]);

            }
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>$e->getMessage(), 'data'=>[]], 500);
        }
    }

    public function updateMemberProfile(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'name'          =>  'required',
                'email'         =>  'required|email|unique:users,id',
                'avatar'        =>  'nullable',
                'role'          =>  'required'
            ]);
            if($validator->fails()){
                $error = $validator->errors()->all()[0];
                return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
            } else {  
                $user = User::find($id);
                if($request->user()->role === UserRole::WORKSPACE_ADMIN && $user && $request->user()->workspace_id === $user->workspace_id ){
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->role = $request->role;
                    if($request->avatar && strcmp($request->avatar, $user->avatar) !== 0) 
                        $user->avatar = $request->avatar;
                $user->update();
                return response()->json(['status'=>'true', 'message'=>'Profile Updated!', 'data'=>$user]);

                if($request->user()->role !== UserRole::WORKSPACE_ADMIN || $request->user()->workspace_id = $user->workspace_id) return response()->json(['status'=>'false', 'message'=>'Forbidden!', 'data'=>[]], 403);
                if(is_null($user)) return response()->json(['status'=>'false', 'message'=>'Member not found!', 'data'=>[]], 404);
                } else return $this->jsonResponse(false, 'Forbidden' ,[], 403);

            }
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>$e->getMessage(), 'data'=>[]], 500);
        }
        
    }

    public function showAvatar($id)
    {

        // $url = User::findOrFail($id)->avatar;
        // $url = 'public/images/avatars/1.png';
        // // $contents = Storage::disk('public')->path($url);
        
        // return response()->file(Storage::get('images/avatars/default.jpg'));

        $avatar = User::findOrFail($id)->avatar;
        if (!Storage::exists($avatar)) {
            return Storage::download(self::DEFAULT_AVATAR);
        }

        return Storage::download($avatar);
    }
    
}
