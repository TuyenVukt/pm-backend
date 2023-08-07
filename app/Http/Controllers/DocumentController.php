<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Enums\UserRole;


class DocumentController extends Controller
{
    public function create (Request $request){
        $validator = Validator::make($request->all(), [
            'name'                =>  'required|string',
            'link'         =>  'required|string',   
            'project_id'         =>  'required',   
        ]);

        if($validator->fails()){
            $error = $validator->errors()->all()[0];
            return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
        } else {
            //check xem có thuộc project này không    
            $document = Document::create([
                'name'                  => $request->name,
                'link'                  => $request->link,
                'description'           => $request->description,
                'project_id'            => $request->project_id,
                'created_by'            => $request->user()->id
            ]);
            return $this->jsonResponse(true, 'File upload successfully!', $document);
        }

    }

    public function update (Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name'                =>  'required|string',
            'link'                =>   'required',
        ]);

        if($validator->fails()){
            $error = $validator->errors()->all()[0];
            return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
        } else {
            $doc = Document::findOrFail($id);
            if(($request->user()->role === UserRole::PM || $request->user()->id == $doc->created_by )){
                $doc->name = $request->name;
                $doc->description = $request->description;
                $doc->link = $request->link;
                $doc->updated_at = Carbon::now();
                $doc->save();
                return $this->jsonResponse(true, 'Document updated successfully!', $doc);
            } else   
                return $this->jsonResponse(false, 'Forbidden', 403);
        }
    }

    public function get ($id){
        //kiểm tra xem có thuộc project này không
        $doc = Document::findOrFail($id);
        return $this->jsonResponse(true, 'File upload successfully!', $doc);
    }

    public function getFilesByProject (Request $request, $project_id){

        $key = $request->input('key');
        $documents = Document::query()-> where('project_id', $project_id);
        if(!empty(trim($key))) $documents = $documents->where('name', 'LIKE', "%$key%");
        $documents = $documents->get();
        return $this->jsonResponse(true, 'Find documents!', $documents);
    }

    public function destroy (Request $request){
        
    }

}

