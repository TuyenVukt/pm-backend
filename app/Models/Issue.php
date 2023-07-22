<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        'title',                
        'description',      
        'project_id',    
        'milestone_id',      
        'status',     
        'category',   
        'priority',
        'created_user_id',
        'asignee_id'
    ];
    
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }


}
