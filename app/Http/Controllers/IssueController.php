<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Issue;

class IssueController extends Controller
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


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    //	id	bigint UN AI PK//
	// title	varchar(255)//
	// description	text//
	// start_time	date//
	// end_time	date//
	// project_id	bigint UN//
	// milestone_id	bigint UN//
	// before_task_id	int UN
	// after_task_id	int UN
	// created_user_id	bigint UN//
	// asignee_id	bigint UN//
	// status	varchar(255)//
	// category_id	varchar(255)//
	// priority	varchar(255)//
	// is_parent	tinyint(1)
	// is_child	tinyint(1)

    //LOW MIDDLE HIGH
    //TASK REQUEST BUG OTHER


    $validator = Validator::make($request->all(), [
        'title'                 =>  'required|string|unique:issues,title,except,id',
        'description'           =>  'required',   
        'start_time'            =>  'nullable|date_format:Y-m-d',
        'end_time'              =>  'nullable|date_format:Y-m-d',
        'project_id'            =>  'required',
        'milestone_id'          =>  'required',
        'status'                =>  'required',
        'category'              =>  'required',
        'priority'              =>  'required',

    ]);

    if($validator->fails()){
        $error = $validator->errors()->all()[0];
        return response()->json(['status'=>'false', 'message'=>$error, 'data'=>[]], 422);
    } else {
        
        $issue = Issue::create([
            'title'                 =>  $request->title,
            'description'           =>  $request->description,   
            'start_time'            =>  $request->start_time,
            'project_id'            =>  $request->project_id,
            'milestone_id'          =>  $request->milestone_id,
            'created_user_id'       =>  $request->user()->id,
            'status'                =>  $request->status,
            'category'              =>  $request->category,
            'priority'              =>  $request->priority,
        ]);

        if($request->start_time) $issue->start_time = $request->start_time;
        if($request->end_time) $issue->end_time = $request->end_time;
        if($request->asignee_id) $issue->asignee_id = $request->asignee_id;

        return response()->json(['status'=>'true', 'message'=>'Issue Created!', 'data'=>$issue]);
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
        //
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
