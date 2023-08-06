<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Milestone;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',          
        'project_key',
        'description',       
        'start_date',  
        'due_date', 
        'workspace_id',  
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_project', 'project_id', 'user_id');
    }

    public function milestones(){
        return $this->hasMany(Milestone::class);
    }
}
