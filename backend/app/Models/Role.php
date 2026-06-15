<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends ErpModel
{
    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'description',
        'guard_name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
