<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Milestone;
use App\Models\Project;

class MilestoneController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        //ai có quyền tạo
        $validator = Validator::make($request->all(), [
            'name'                =>  'required|string',
            'description'          =>  'nullable',   
            'start_date'           =>  'nullable', 
            'due_date'             =>  'nullable',
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(1){
            $milestone = Milestone::find($id);
            if($milestone) return response()->json(['status'=>'true', 'message'=>'Details of  Milestone ', 'data'=>$milestone]);
            return response()->json(['status'=>'false', 'message'=>'Milestone not found! ', 'data'=>[]], 404);
        }
    }

    public function getMilestoneByProject($id)
    {
        try{
            $project = Project::findOrFail($id);
            $milestone = $project->milestones;
            return response()->json(['status'=>'true', 'message'=>'Get milestones !', 'data'=>$milestone]);
        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Get milestones failed!', 'data'=>[]], 500);
        }
        

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

                $milestone->name = $request->name;
                $milestone->description = $request->description;
                $milestone->start_date = $request->start_date ? $request->start_date : Carbon::now()->format('Y-m-d');
                $milestone->due_date = $request->due_date ? $request->due_date : Carbon::now()->format('Y-m-d');

                $milestone->update();

                return response()->json(['status'=>'true', 'message'=>'Milestone Updated!', 'data'=>$milestone]);

            }



        } catch (\Exception $e){
            return response()->json(['status'=>'false', 'message'=>'Get milestones failed!', 'data'=>[]], 500);
        }
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
