<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'mac_admin';
    protected $primaryKey = 'admin_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getAuthPassword()
    {
        return $this->admin_pwd;
    }
    
    // Helper for status
    public function getStatusTextAttribute()
    {
        return $this->admin_status == 1 ? 'Enabled' : 'Disabled';
    }
}
