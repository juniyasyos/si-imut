<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label', 'guard_name'];
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    // Cek apakah role ini adalah super_admin
    public function isSuperAdmin()
    {
        return $this->name === 'super_admin';
    }

    // Removed boot method restrictions - super_admin can now be edited
}
