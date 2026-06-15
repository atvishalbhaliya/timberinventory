<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class ErpModel extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = ['id'];
}
