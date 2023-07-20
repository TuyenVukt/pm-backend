<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',          
        'organization_name',
        'domain',       
        'secret_code',  
        'secret_key',  
        'description',  
        'workspace_admin_id'   
    ];

    protected $hidden = ['secret_code', 'secret_key', 'workspace_admin_id'];
}
