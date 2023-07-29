<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        // 'task_key',
        'name',                
        'description',      
        // 'start_time',    
        // 'end_time',      
        'project_id',     
        'milestone_id',   
        // 'estimate_time',
        // 'is_day',//
        // 'before_task_id',//
        // 'after_task_id',//
        'created_by',//?
        // 'asignee_id',//?
        'status',
        // 'category',
        'priority',
        // 'is_parent',
        // 'is_child'//






    ];
    
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
