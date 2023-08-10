<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\Task;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'  ,
        'description',
        'project_id',
        'start_date',
        'due_date',
        'project_id',
        'created_by'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }


    public function tasks(){
        return $this->hasMany(Task::class);
    }
}
